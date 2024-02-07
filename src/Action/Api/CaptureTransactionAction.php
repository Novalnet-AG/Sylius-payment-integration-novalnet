<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action\Api;

use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCode;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\TransactionNotes;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CaptureTransaction;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionHistoryActionInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionsActionInterface;
use Payum\Core\Action\ActionInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureTransactionAction extends ApiAwareAction implements ActionInterface
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

        $refundResponse = $this->novalnetApiClient->captureTransaction([
            'transaction' => [
                'tid' => $transaction->getTid(),
            ],
            'custom' => [
                'lang' => $this->customLangCode->getLangCode(),
                'shop_invoked' => 1,
            ],
        ]);

        if ($this->novalnetApiClient->isSuccessApi($refundResponse)) {
            $this->transactionHistory->addNote(
                $this->transactionNotes->getConfirmNote(),
                $payment->getOrder()->getId(),
                $payment->getId(),
            );
            $transaction->setGatewayStatus($refundResponse['transaction']['status']);
            $this->novalentTransactions->updateTransaction($transaction);

            return;
        }

        throw new \Exception($this->novalnetApiClient->getApiResponseStatusText($refundResponse));
    }

    public function supports($request): bool
    {
        return
            $request instanceof CaptureTransaction &&
            $request->getModel() instanceof PaymentInterface;
    }
}
