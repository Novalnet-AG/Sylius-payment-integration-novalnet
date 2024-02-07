<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Factory;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionHistoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class NovalnetTransactionHistoryFactory implements NovalnetTransactionHistoryFactoryInterface
{
    public function __construct(
        private FactoryInterface $factory,
    ) {
    }

    public function createNew(): NovalnetTransactionHistoryInterface
    {
        /** @var NovalnetTransactionHistoryInterface $transactionHistoryFactory */
        $transactionHistoryFactory = $this->factory->createNew();

        return $transactionHistoryFactory;
    }

    public function create(
        string $message,
        int $orderId,
        int $paymentId,
        bool $private,
    ): NovalnetTransactionHistoryInterface {
        $novalnetTransctionHistory = $this->createNew();
        $novalnetTransctionHistory->setNote($message);
        $novalnetTransctionHistory->setOrderId($orderId);
        $novalnetTransctionHistory->setPaymentId($paymentId);
        $novalnetTransctionHistory->setPrivate($private);
        $novalnetTransctionHistory->setDate(new \DateTime('now'));

        return $novalnetTransctionHistory;
    }
}
