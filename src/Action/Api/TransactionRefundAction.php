<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action\Api;

use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCode;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\TransactionNotes;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\RefundTransaction;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionHistoryActionInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionsActionInterface;
use Payum\Core\Action\ActionInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class TransactionRefundAction extends ApiAwareAction implements ActionInterface
{
    public function __construct(
        private NovalnetTransactionsActionInterface $novalentTransactions,
        private NovalnetTransactionHistoryActionInterface $transactionHistory,
        private TransactionNotes $transactionNotes,
        private CustomLangCode $customLangCode,
    ) {
    }

    public function execute($request): void
    {
        $payment = $request->getModel();

        $transaction = $this->novalentTransactions->findOneBy(['payment_id' => $payment->getId()]);

        if (null === $transaction) {
            return;
        }

        $refundAmount = ($transaction->getAmount() - $transaction->getRefundAmount());

        $refundResponse = $this->novalnetApiClient->createRefund([
            'transaction' => [
                'tid' => $transaction->getTid(),
                'amount' => $refundAmount,
            ],
            'custom' => [
                'lang' => $this->customLangCode->getLangCode(),
                'shop_invoked' => 1,
            ],
        ]);

        if ($this->novalnetApiClient->isSuccessApi($refundResponse)) {
            $this->transactionHistory->addNote(
                $this->transactionNotes->getRefundNote($refundResponse),
                $payment->getOrder()->getId(),
                $payment->getId(),
            );
            $refundedAmount = (isset($refundResponse['transaction']['refund']['amount'])) ? $transaction->getRefundAmount() + (int) $refundResponse['transaction']['refund']['amount'] : $transaction->getRefundAmount();
            $transaction->setRefundAmount($refundedAmount);
            if ($refundResponse['transaction']['status'] === $this->novalnetApiClient::STATUS_DEACTIVATED) {
                $transaction->setGatewayStatus($refundResponse['transaction']['status']);
            }
            $this->novalentTransactions->updateTransaction($transaction);

            return;
        }

        throw new \Exception($this->novalnetApiClient->getApiResponseStatusText($refundResponse));
    }

    public function supports($request): bool
    {
        return
            $request instanceof RefundTransaction &&
            $request->getModel() instanceof PaymentInterface;
    }
}
