<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Factory;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionsInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class NovalnetTransactionsFactory implements NovalnetTransactionsFactoryInterface
{
    public function __construct(
        private FactoryInterface $factory,
    ) {
        $this->factory = $factory;
    }

    public function createNew(): NovalnetTransactionsInterface
    {
        /** @var NovalnetTransactionsInterface $transactionFactory */
        $transactionFactory = $this->factory->createNew();

        return $transactionFactory;
    }

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
    ): NovalnetTransactionsInterface {
        $novalnetTransction = $this->createNew();
        $novalnetTransction->setPaymentId($paymentId);
        $novalnetTransction->setOrderId($orderId);
        $novalnetTransction->setTid($tid);
        $novalnetTransction->setAmount($txnAmount);
        $novalnetTransction->setPaidAmount(0);
        $novalnetTransction->setRefundAmount(0);
        $novalnetTransction->setPaymentType($novalnetPaymentType);
        $novalnetTransction->setGatewayStatus($gatewayStatus);
        $novalnetTransction->setCurrency($currency);
        $novalnetTransction->setDetails($txnDetails);

        return $novalnetTransction;
    }
}
