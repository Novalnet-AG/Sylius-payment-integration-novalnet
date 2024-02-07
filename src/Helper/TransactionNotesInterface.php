<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Helper;

interface TransactionNotesInterface
{
    public function getReminderNote($count): string;

    public function getRefundNote($responseData): string;

    public function getConfirmNote(): string;

    public function getUpdateNote($eventData): string;

    public function getCreditNote($eventParentTID, $eventData): string;

    public function getChargebackNote($eventParentTID, $eventData): string;

    public function getCollectionNote($collectionReference): string;

    public function getTidNotes($transactionData, string|null $errorNote = null): string;
}
