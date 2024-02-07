<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Transaction;

use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetTransactionHistoryFactory;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class NovalnetTransactionHistoryAction implements NovalnetTransactionHistoryActionInterface
{
    public function __construct(
        private NovalnetTransactionHistoryFactory $transactionHistoryFactory,
        private RepositoryInterface $repository,
    ) {
    }

    public function addNote(
        string $message,
        int $orderId,
        int $paymentId,
    ): void {
        $transactionHistory = $this->transactionHistoryFactory->create($message, $orderId, $paymentId, false);
        $this->repository->add($transactionHistory);
    }

    public function addPrivateNote(
        string $message,
        int $orderId,
        int $paymentId,
    ): void {
        $transactionHistory = $this->transactionHistoryFactory->create($message, $orderId, $paymentId, true);
        $this->repository->add($transactionHistory);
    }
}
