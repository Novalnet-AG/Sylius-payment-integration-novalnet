<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Transaction;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionsInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface NovalnetTransactionsActionInterface
{
    public function recordeTransaction(
        PaymentInterface $payment,
        OrderInterface $order,
        array $transaction,
    ): void;

    public function findOneByTID($tid): ?NovalnetTransactionsInterface;

    public function findOneBy(array $criteria): ?NovalnetTransactionsInterface;

    public function updateTransaction(NovalnetTransactionsInterface $transaction);

    public function findOneByTidOrPaymentId($tid, $paymentId): ?NovalnetTransactionsInterface;
}
