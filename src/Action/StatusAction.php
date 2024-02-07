<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Action\Api\ApiAwareAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCodeInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\TransactionNotesInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Sender\PaymentDetailsEmailSenderInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionHistoryActionInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionsActionInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class StatusAction extends ApiAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private RequestStack $requestStack,
        private PaymentDetailsEmailSenderInterface $paymentDetailsEmailSender,
        private NovalnetTransactionHistoryActionInterface $transactionHistory,
        private NovalnetTransactionsActionInterface $transaction,
        private TransactionNotesInterface $transactionNotes,
        private CustomLangCodeInterface $customLangCode,
    ) {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (!isset($details['txnStatus']) && !isset($details['txnSecret'])) {
            $request->markNew();

            return;
        }

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $errorInfo = null;

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['status']) && !isset($details['novalnetTxnUpdated'])) {
            if (isset($httpRequest->query['checksum'], $httpRequest->query['tid'])) {
                if ($this->novalnetApiClient->verifyChecksum($httpRequest->query, $details['txnSecret'] ?? $httpRequest->query['txn_secret'])) {
                    $parameters = [
                        'transaction' => [
                            'tid' => $httpRequest->query['tid'],
                        ],
                        'custom' => [
                            'lang' => $this->customLangCode->getLangCode($order->getLocaleCode()),
                        ],
                    ];
                    $transactionDetails = $this->novalnetApiClient->getTransactionDetails($parameters);

                    if (isset($transactionDetails['transaction']['status']) && $this->novalnetApiClient::STATUS_FAILURE !== $transactionDetails['transaction']['status']) {
                        $details['transactionData'] = [
                            'paymentType' => $transactionDetails['transaction']['payment_type'],
                            'tid' => $transactionDetails['transaction']['tid'],
                        ];
                        $details['novalnetTxnUpdated'] = true;

                        $this->transaction->recordeTransaction($payment, $payment->getOrder(), $transactionDetails);
                        $this->paymentDetailsEmailSender->sendConfirmationEmail($payment);
                    }

                    $details['txnStatus'] = $transactionDetails['transaction']['status'] ?? $transactionDetails['result']['status'];
                    if (in_array($details['txnStatus'], [$this->novalnetApiClient::STATUS_FAILURE, $this->novalnetApiClient::API_STATUS_FAILURE], true)) {
                        $errorInfo = $this->novalnetApiClient->getApiResponseStatusText($transactionDetails, 'Payment Failed');
                    }
                } else {
                    $details['txnStatus'] = $this->novalnetApiClient::API_STATUS_FAILURE;
                    $errorInfo = 'While redirecting some data has been changed. The hash check failed';
                }
            } else {
                $details['txnStatus'] = $httpRequest->query['status'];
                $errorInfo = $this->novalnetApiClient->getRedirectReturnStatusText($httpRequest->query, 'Payment Canceled');
            }
            if (null !== $errorInfo) {
                /** @var FlashBagInterface $flashBag */
                $flashBag = $this->requestStack->getSession()->getBag('flashes');
                $flashBag->add('error', $errorInfo);
                $details['novalnetTxnUpdated'] = true;
                $noteText = $this->transactionNotes->getTidNotes($httpRequest->query, $errorInfo);
                $this->transactionHistory->addNote($noteText, $order->getId(), $payment->getId());
            }
        }

        switch ($details['txnStatus']) {
            case $this->novalnetApiClient::STATUS_PENDING:
                $request->markPending();

                break;
            case $this->novalnetApiClient::STATUS_ON_HOLD:
                $request->markAuthorized();

                break;
            case $this->novalnetApiClient::STATUS_CONFIRMED:
                $request->markCaptured();

                break;
            case $this->novalnetApiClient::STATUS_DEACTIVATED:
                $request->markCanceled();

                break;
            case $this->novalnetApiClient::STATUS_FAILURE:
            case $this->novalnetApiClient::API_STATUS_FAILURE:
                $request->markFailed();

                break;
            default:
                $request->markUnknown();

                break;
        }

        $payment->setDetails($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
