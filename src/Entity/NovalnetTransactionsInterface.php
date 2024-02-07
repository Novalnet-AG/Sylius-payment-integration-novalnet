<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface NovalnetTransactionsInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getPaymentId(): ?int;

    public function setPaymentId(int $payment_id): static;

    public function getOrderId(): ?int;

    public function setOrderId(int $order_id): static;

    public function getTid(): ?string;

    public function setTid(string $tid): static;

    public function getPaymentType(): ?string;

    public function setPaymentType(string $payment_type): static;

    public function getAmount(): ?int;

    public function setAmount(int $transaction_amount): static;

    public function getPaidAmount(): ?int;

    public function setPaidAmount(int $paid_amount): static;

    public function getRefundAmount(): ?int;

    public function setRefundAmount(int $refund_amount): static;

    public function getGatewayStatus(): ?string;

    public function setGatewayStatus(string $gateway_status): static;

    public function getDetails(): ?array;

    public function setDetails(?array $details): static;

    public function getCurrency(): ?string;

    public function setCurrency(string $currency): static;
}
