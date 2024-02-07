<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Factory;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionsInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface NovalnetTransactionsFactoryInterface extends FactoryInterface
{
    public function create(
        int $paymentId,
        int $orderId,
        string $tid,
        int $txnAmount,
        int $paidAmount,
        int $refundAmount,
        string $novalnetPaymentType,
        string $gatewayStatus,
        string $currency,
        array $txnDetails = [],
    ): NovalnetTransactionsInterface;
}
