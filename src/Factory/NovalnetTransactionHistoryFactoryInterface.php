<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Factory;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionHistoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface NovalnetTransactionHistoryFactoryInterface extends FactoryInterface
{
    public function create(
        string $message,
        int $logLevel,
        int $errorCode,
        bool $private,
    ): NovalnetTransactionHistoryInterface;
}
