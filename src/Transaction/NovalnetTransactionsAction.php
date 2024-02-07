<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Transaction;

use Doctrine\ORM\EntityManagerInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionsInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetTransactionsFactory;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class NovalnetTransactionsAction implements NovalnetTransactionsActionInterface
{
    public function __construct(
        private NovalnetTransactionsFactory $transactionsFactory,
        private RepositoryInterface $repository,
        private EntityManagerInterface $entityMangaer,
    ) {
    }

    public function recordeTransaction(
        PaymentInterface $payment,
        OrderInterface $order,
        array $transaction,
    ): void {
        $transaction = $this->transactionsFactory->create(
            $payment->getId(),
            $order->getId(),
            (string) $transaction['transaction']['tid'],
            $transaction['transaction']['amount'],
            0,
            0,
            $transaction['transaction']['payment_type'],
            $transaction['transaction']['status'],
            $payment->getCurrencyCode(),
            $this->getTransactionDetails($transaction),
        );
        $this->repository->add($transaction);
    }

    public function findOneByTID(
        $tid,
    ): ?NovalnetTransactionsInterface {
        return $this->repository->findOneBy(['tid' => $tid]);
    }

    public function findOneByTidOrPaymentId($tid, $paymentId): ?NovalnetTransactionsInterface
    {
        return $this->findOneByTID($tid) ?? $this->repository->findOneBy(['payment_id' => $paymentId]);
    }

    public function findOneBy(
        array $criteria,
    ): ?NovalnetTransactionsInterface {
        return $this->repository->findOneBy($criteria);
    }

    public function updateTransaction(NovalnetTransactionsInterface $transaction): void
    {
        $this->entityMangaer->persist($transaction);
        $this->entityMangaer->flush();
    }

    private function getTransactionDetails(array $transaction): array
    {
        $details = [
            'testMode' => $transaction['transaction']['test_mode'],
        ];

        if (isset($transaction['transaction']['bank_details'])) {
            $details['bankDetails'] = $transaction['transaction']['bank_details'];

            $details['paymentReference'] = [
                $transaction['transaction']['tid'],
            ];
            if (isset($transaction['transaction']['invoice_ref'])) {
                $details['paymentReference'][] = $transaction['transaction']['invoice_ref'];
            }
        }

        if (isset($transaction['transaction']['partner_payment_reference'])) {
            $details['partnerPaymentReference'] = $transaction['transaction']['partner_payment_reference'];
        }

        if (isset($transaction['transaction']['due_date'])) {
            $details['dueDate'] = $transaction['transaction']['due_date'];
        }

        if (isset($transaction['transaction']['nearest_stores'])) {
            $details['nearestStores'] = $transaction['transaction']['nearest_stores'];
        }

        if (in_array($transaction['transaction']['payment_type'], ['GOOGLEPAY', 'APPLEPAY'], true)) {
            if (isset($transaction['transaction']['payment_data']['card_brand'])) {
                $details['walletCard'] = [
                    'brand' => $transaction['transaction']['payment_data']['card_brand'],
                    'lastFour' => $transaction['transaction']['payment_data']['last_four'],
                ];
            }
        }

        return $details;
    }
}
