<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Action\Api\ApiAwareAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionsInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\TransactionNotes;
use Novalnet\SyliusNovalnetPaymentPlugin\Sender\PaymentDetailsEmailSenderInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionHistoryActionInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionsActionInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachineInterface;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NotifyAction extends ApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var array */
    private $eventData;

    /** @var string */
    private $eventType;

    /** @var mixed */
    private $eventTID;

    /** @var mixed */
    private $eventParentTID;

    /** @var ArrayObject */
    private $eventResponseData;

    /** @var StateMachineInterface */
    private $paymentStateMachine;

    /** @var PaymentInterface */
    private $payment;

    /** @var OrderInterface */
    private $order;

    /** @var NovalnetTransactionsInterface */
    private $transaction;

    /** @var ArrayObject */
    private $transactionDetails;

    /** @var bool */
    private $updateTransaction;

    /** @var bool */
    private $setPrivateNote;

    public function __construct(
        private FactoryInterface $stateMachineFactory,
        private MoneyFormatterInterface $moneyFormatter,
        private TranslatorInterface $translator,
        private PaymentRepositoryInterface $paymentRepository,
        private NovalnetTransactionsActionInterface $novalentTransactions,
        private NovalnetTransactionHistoryActionInterface $transactionHistory,
        private TransactionNotes $transactionNotes,
        private PaymentDetailsEmailSenderInterface $emailSender,
    ) {
        $this->eventResponseData = new ArrayObject();
        $this->updateTransaction = true;
        $this->setPrivateNote = false;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->eventData = ArrayObject::ensureArrayObject($request->getModel())->toUnsafeArray();
        $this->setEventData();
        $this->getTransaction();

        if (null === $this->transaction) {
            throw new NotFoundHttpException('Novalnet Transaction not found');
        }
        $this->setPaymentAndStateMachine($this->transaction->getPaymentId());

        $this->transactionDetails = ArrayObject::ensureArrayObject($this->transaction->getDetails());

        if ($this->novalnetApiClient->isSuccessApi($this->eventData)) {
            switch($this->eventType) {
                case 'PAYMENT':
                    throw new HttpResponse('Novalnet callback received', 200);
                case 'TRANSACTION_CAPTURE':
                    $this->transactionCapture();

                    break;
                case 'TRANSACTION_CANCEL':
                    $this->transactionCancel();

                    break;
                case 'TRANSACTION_REFUND':
                    $this->transactionRefund();

                    break;
                case 'TRANSACTION_UPDATE':
                    $this->transactionUpdate();

                    break;
                case 'CREDIT':
                    $this->credit();

                    break;
                case 'CHARGEBACK':
                    $this->setPrivateNote = true;
                    $this->chargeback();

                    break;
                case 'PAYMENT_REMINDER_1':
                case 'PAYMENT_REMINDER_2':
                    $this->setPrivateNote = true;
                    $this->paymentReminder();

                    break;
                case 'SUBMISSION_TO_COLLECTION_AGENCY':
                    $this->setPrivateNote = true;
                    $this->submissionToCollection();

                    break;
                default:
                    throw new HttpResponse(sprintf('The webhook notification has been received for the unhandled EVENT type(%s)', $this->eventType), 200);
            }

            if (empty($this->eventResponseData)) {
                throw new HttpResponse('Empty Response', 200);
            }

            if (null !== $this->eventResponseData->get('message')) {
                if ($this->setPrivateNote) {
                    $this->transactionHistory->addPrivateNote(
                        $this->eventResponseData->get('transactionNote', $this->eventResponseData->get('message')),
                        $this->order->getId(),
                        $this->payment->getId(),
                    );
                } else {
                    $this->transactionHistory->addNote(
                        $this->eventResponseData->get('transactionNote', $this->eventResponseData->get('message')),
                        $this->order->getId(),
                        $this->payment->getId(),
                    );
                }
                $this->eventResponseData->offsetUnset('transactionNote');
            }

            if ($this->updateTransaction) {
                $this->transaction->setDetails($this->transactionDetails->toUnsafeArray());
                $this->novalentTransactions->updateTransaction($this->transaction);
            }

            throw new HttpResponse(json_encode($this->eventResponseData));
        } else {
            throw new HttpResponse('Novalnet callback received', 200);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof ArrayObject
        ;
    }

    private function transactionCapture(): void
    {
        $this->applyPaymentTransition(PaymentTransitions::TRANSITION_COMPLETE);
        $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);
        if ('INVOICE' !== $this->eventData['transaction']['payment_type']) {
            $this->transaction->setPaidAmount($this->eventData['transaction']['amount']);
        }

        if (isset($this->eventData['transaction']['due_date'])) {
            $this->transactionDetails->offsetSet('dueDate', $this->eventData['transaction']['due_date']);
        }

        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getConfirmNote(),
        );
    }

    private function transactionCancel(): void
    {
        $this->applyPaymentTransition(PaymentTransitions::TRANSITION_CANCEL);
        $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);
        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getCancelNote(),
        );
    }

    private function transactionRefund(): void
    {
        if (!empty($this->eventData['transaction']['refund']['amount'])) {
            $refundedAmount = $this->transaction->getRefundAmount() + (int) $this->eventData['transaction']['refund']['amount'];
            if ($refundedAmount >= $this->transaction->getAmount()) {
                $this->applyPaymentTransition(PaymentTransitions::TRANSITION_REFUND);
            }

            $this->transaction->setRefundAmount($refundedAmount);
            $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);
            $this->eventResponseData->offsetSet(
                'message',
                $this->transactionNotes->getRefundNote($this->eventData),
            );
        }
    }

    private function transactionUpdate(): void
    {
        if ('DEACTIVATED' === $this->eventData['transaction']['status']) {
            $this->applyPaymentTransition(PaymentTransitions::TRANSITION_CANCEL);
            $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);
            $message = $this->transactionNotes->getCancelNote();
        } elseif (in_array($this->transaction->getGatewayStatus(), ['PENDING', 'ON_HOLD'], true)) {
            if ('ON_HOLD' === $this->eventData['transaction']['status']) {
                $this->applyPaymentTransition(PaymentTransitions::TRANSITION_AUTHORIZE);
            } elseif ('CONFIRMED' === $this->eventData['transaction']['status']) {
                $this->applyPaymentTransition(PaymentTransitions::TRANSITION_COMPLETE);
                $this->transaction->setPaidAmount($this->transaction->getAmount());
            }
            $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);

            if ((int) $this->eventData['transaction']['amount'] !== $this->transaction->getAmount() && !in_array($this->transaction->getPaymentType(), ['INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA'], true)) {
                $this->transactionDetails->offsetSet('originalTxnAmount', $this->transaction->getAmount());
                $this->transaction->setAmount((int) $this->eventData['transaction']['amount']);
            }

            if (
                in_array($this->eventData['transaction']['payment_type'], ['INVOICE', 'PREPAYMENT', 'GUARANTEED_INVOICE'], true) &&
                !empty($this->eventData['transaction']['bank_details']) &&
                (empty($this->transactionDetails->offsetGet('bankDetails')) || ($this->transactionDetails->offsetGet('bankDetails') !== $this->eventData['transaction']['bank_details']))
            ) {
                $this->transactionDetails->offsetSet('bankDetails', $this->eventData['transaction']['bank_details']);
            }

            if (
                'CASHPAYMENT' === $this->eventData['transaction']['payment_type'] &&
                !empty($this->eventData['transaction']['nearest_stores']) &&
                (empty($this->transactionDetails->offsetGet('nearestStores')) || ($this->transactionDetails->offsetGet('nearestStores') !== $this->eventData['transaction']['nearest_stores']))
            ) {
                $this->transactionDetails->offsetSet('nearestStores', $this->eventData['transaction']['nearest_stores']);
            }

            if (isset($this->eventData['transaction']['due_date'])) {
                $this->transactionDetails->offsetSet('dueDate', $this->eventData['transaction']['due_date']);
            }

            $message = $this->transactionNotes->getUpdateNote($this->eventData);
        }
        $this->eventResponseData->offsetSet(
            'message',
            $message,
        );
    }

    private function credit(): void
    {
        if (in_array($this->eventData['transaction']['payment_type'], ['INVOICE_CREDIT', 'CASHPAYMENT_CREDIT', 'MULTIBANCO_CREDIT'], true)) {
            if ($this->transaction->getPaidAmount() < $this->transaction->getAmount()) {
                $paidAmount = $this->transaction->getPaidAmount() + (int) ($this->eventData['transaction']['amount']);
                $amountToPaid = $this->transaction->getAmount() - $this->transaction->getRefundAmount();

                if ($paidAmount >= $amountToPaid) {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_COMPLETE);
                }

                $this->transaction->setPaidAmount($paidAmount);
                $this->transaction->setGatewayStatus($this->eventData['transaction']['status']);
            }
        }
        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getCreditNote($this->eventParentTID, $this->eventData),
        );
    }

    private function chargeback(): void
    {
        $this->updateTransaction = false;
        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getChargebackNote($this->eventParentTID, $this->eventData),
        );
    }

    private function paymentReminder(): void
    {
        $this->updateTransaction = false;
        $explodedType = explode('_', $this->eventType);
        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getReminderNote(end($explodedType)),
        );
    }

    private function submissionToCollection(): void
    {
        $this->updateTransaction = false;
        $this->eventResponseData->offsetSet(
            'message',
            $this->transactionNotes->getCollectionNote($this->eventData['collection']['reference']),
        );
    }

    private function applyPaymentTransition(string $transition): void
    {
        try {
            if ($this->paymentStateMachine->can($transition)) {
                $this->paymentStateMachine->apply($transition);
            }
        } catch(SMException $e) {
            throw new HttpResponse($e->getMessage());
        }
    }

    private function setEventData(): void
    {
        $this->eventType = $this->eventData['event']['type'];
        $this->eventParentTID = $this->eventTID = $this->eventData['event']['tid'];
        if (isset($this->eventData['event']['parent_tid'])) {
            $this->eventParentTID = $this->eventData['event']['parent_tid'];
        }
    }

    private function setPaymentAndStateMachine(int|string $paymentId): void
    {
        if (!empty($this->payment) && !empty($this->paymentStateMachine) && $this->payment->getId() == $paymentId) {
            return;
        }
        $this->payment = $this->paymentRepository->findOneBy(['id' => $paymentId]);
        if (null === $this->payment) {
            throw new NotFoundHttpException('Payment not found');
        }
        $this->order = $this->payment->getOrder();
        $this->paymentStateMachine = $this->stateMachineFactory->get($this->payment, PaymentTransitions::GRAPH);
    }

    private function getTransaction(): void
    {
        $this->transaction = $this->novalentTransactions->findOneByTidOrPaymentId(
            $this->eventParentTID,
            $this->eventData['custom']['payment_id'] ?? '',
        );

        if (null === $this->transaction && !empty($this->eventData['custom']['payment_id'])) {
            $this->setPaymentAndStateMachine($this->eventData['custom']['payment_id']);
            if ('ONLINE_TRANSFER_CREDIT' === $this->eventData['transaction']['payment_type']) {
                $transactionTID = $this->eventData['transaction']['tid'];
                // Update the transaction TID for updating the initial payment.
                $this->eventData['transaction']['tid'] = $this->eventParentTID;
                $this->updateInitialPayment();
                // Reassign the transaction TID after the initial payment is updated.
                $this->eventData['transaction']['tid'] = $transactionTID;
            } elseif ('PAYMENT' === $this->eventType) {
                $this->updateInitialPayment();
            } else {
                throw new NotFoundHttpException('Order reference not found in the shop');
            }
        }
    }

    private function updateInitialPayment(): void
    {
        $details = $this->payment->getDetails();
        $details['txnStatus'] = $this->eventData['transaction']['status'] ?? $this->eventData['result']['status'];
        $details['transactionData'] = [
            'paymentType' => $this->eventData['transaction']['payment_type'],
            'tid' => $this->eventData['transaction']['tid'],
        ];
        if (!isset($details['novalnetTxnUpdated'])) {
            $details['novalnetTxnUpdated'] = true;
            $this->payment->setDetails($details);
            $this->paymentRepository->add($this->payment);
            if ($this->novalnetApiClient->isSuccessApi($this->eventData)) {
                if ($this->novalnetApiClient::STATUS_CONFIRMED == $this->eventData['transaction']['status']) {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_COMPLETE);
                } elseif ($this->novalnetApiClient::STATUS_ON_HOLD == $this->eventData['transaction']['status']) {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_AUTHORIZE);
                } elseif ($this->novalnetApiClient::STATUS_PENDING == $this->eventData['transaction']['status']) {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_PROCESS);
                } else {
                    throw new HttpResponse('Invalid Transaction status');
                }
                $this->novalentTransactions->recordeTransaction($this->payment, $this->payment->getOrder(), $this->eventData);
                $this->emailSender->sendConfirmationEmail($this->payment);
            } else {
                if ($this->novalnetApiClient::STATUS_DEACTIVATED == $this->eventData['transaction']['status']) {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_CANCEL);
                } else {
                    $this->applyPaymentTransition(PaymentTransitions::TRANSITION_FAIL);
                }
                $tidNote = $this->transactionNotes->getTidNotes(
                    $this->eventData,
                    $this->novalnetApiClient->getApiResponseStatusText($this->eventData),
                );
                $this->transactionHistory->addNote(
                    $tidNote,
                    $this->order->getId(),
                    $this->payment->getId(),
                );

                throw new HttpResponse('Novalnet callback received', 200);
            }
        }
        $this->transaction = $this->novalentTransactions->findOneByTidOrPaymentId(
            $this->eventParentTID,
            $this->eventData['custom']['payment_id'] ?? '',
        );
    }
}
