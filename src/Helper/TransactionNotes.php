<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Helper;

use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TransactionNotes implements TransactionNotesInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private MoneyFormatterInterface $moneyFormatter,
    ) {
        $this->translator = $translator;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function getReminderNote($count): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.remainder',
            [
                '%remainderCount%' => $count,
            ],
        );
    }

    public function getRefundNote($responseData): string
    {
        $refundAmountString = $this->moneyFormatter->format($responseData['transaction']['refund']['amount'], $responseData['transaction']['currency']);
        if (!empty($responseData['transaction']['refund']['tid'])) {
            return $this->translator->trans(
                'novalnet_sylius_novalnet_payment_plugin.transaction.refund_note_with_tid',
                [
                    '%parentTID%' => $responseData['transaction']['tid'],
                    '%refundAmount%' => $refundAmountString,
                    '%refundTID%' => $responseData['transaction']['refund']['tid'],
                ],
            );
        }

        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.refund_note',
            [
                '%parentTID%' => $responseData['transaction']['tid'],
                '%refundAmount%' => $refundAmountString,
            ],
        );
    }

    public function getConfirmNote(): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.capture',
            [
                '%date%' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function getCancelNote(): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.cancel',
            [
                '%date%' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function getUpdateNote($eventData): string
    {
        $message = $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.amount_update',
            [
                '%tid%' => $eventData['transaction']['tid'],
                '%amount%' => $this->moneyFormatter->format($eventData['transaction']['amount'], $eventData['transaction']['currency']),
            ],
        );
        if (isset($eventData['transaction']['update_type']) && in_array($eventData['transaction']['update_type'], ['AMOUNT', 'AMOUNT_DUE_DATE', 'DUE_DATE'], true)) {
            if ('DUE_DATE' === $eventData['transaction']['update_type']) {
                $message = $this->translator->trans(
                    'novalnet_sylius_novalnet_payment_plugin.transaction.due_date_update',
                    [
                        '%tid%' => $eventData['transaction']['tid'],
                        '%dueDate%' => $eventData['transaction']['due_date'],
                    ],
                );
            } elseif ('AMOUNT_DUE_DATE' === $eventData['transaction']['update_type']) {
                $message = $this->translator->trans(
                    'novalnet_sylius_novalnet_payment_plugin.transaction.amount_due_date_update',
                    [
                        '%tid%' => $eventData['transaction']['tid'],
                        '%amount%' => $this->moneyFormatter->format($eventData['transaction']['amount'], $eventData['transaction']['currency']),
                        '%dueDate%' => $eventData['transaction']['due_date'],
                    ],
                );
            }
        }

        return $message;
    }

    public function getCreditNote($eventParentTID, $eventData): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.credit',
            [
                '%parentTID%' => $eventParentTID,
                '%creditAmount%' => $this->moneyFormatter->format($eventData['transaction']['amount'], $eventData['transaction']['currency']),
                '%date%' => (new \DateTime())->format('Y-m-d H:i:s'),
                '%creditTID%' => $eventData['transaction']['tid'],
            ],
        );
    }

    public function getChargebackNote($eventParentTID, $eventData): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.chargeback',
            [
                '%parentTID%' => $eventParentTID,
                '%amount%' => $this->moneyFormatter->format($eventData['transaction']['amount'], $eventData['transaction']['currency']),
                '%date%' => (new \DateTime())->format('Y-m-d H:i:s'),
                '%eventTID%' => $eventData['transaction']['tid'],
            ],
        );
    }

    public function getCollectionNote($collectionReference): string
    {
        return $this->translator->trans(
            'novalnet_sylius_novalnet_payment_plugin.transaction.collection',
            [
                '%collectionReference%' => $collectionReference,
            ],
        );
    }

    public function getTidNotes($transactionData, string|null $errorNote = null): string
    {
        $tidNote = '';
        if (isset($transactionData['payment_type']) || isset($transactionData['transaction']['payment_type'])) {
            $paymentType = $transactionData['payment_type'] ?? $transactionData['transaction']['payment_type'];
            $tidNote .= $this->translator->trans(
                'novalnet_sylius_novalnet_payment_plugin.payment_title.' . strtolower($paymentType),
            );
            $tidNote .= \PHP_EOL;
        }

        if (!empty($transactionData['tid']) || isset($transactionData['transaction']['tid'])) {
            $tidNote .= $this->translator->trans(
                'novalnet_sylius_novalnet_payment_plugin.note.novanet_transaction_id',
                [
                    '%tid%' => $transactionData['tid'] ?? $transactionData['transaction']['tid'],
                ],
            );
            $tidNote .= \PHP_EOL;
        }

        if ($errorNote !== null) {
            $tidNote .= $errorNote;
        }

        return $tidNote;
    }
}
