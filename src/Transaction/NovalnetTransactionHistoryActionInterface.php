<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Transaction;

interface NovalnetTransactionHistoryActionInterface
{
    public function addNote(
        string $note,
        int $orderID,
        int $paymentID,
    ): void;

    public function addPrivateNote(
        string $note,
        int $orderID,
        int $paymentID,
    ): void;
}
