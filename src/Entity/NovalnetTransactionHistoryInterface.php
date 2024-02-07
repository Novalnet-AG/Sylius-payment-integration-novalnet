<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface NovalnetTransactionHistoryInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getOrderId(): ?int;

    public function setOrderId(int $order_id): static;

    public function getPaymentId(): ?int;

    public function setPaymentId(int $payment_id): static;

    public function getNote(): ?string;

    public function setNote(?string $note): static;

    public function isPrivate(): ?bool;

    public function setPrivate(?bool $private): static;

    public function getDate(): ?\DateTimeInterface;

    public function setDate(\DateTimeInterface $date): static;
}
