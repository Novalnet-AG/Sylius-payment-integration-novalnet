<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novalnet\SyliusNovalnetPaymentPlugin\Repository\NovalnetTransactionsRepository;

#[ORM\Entity(repositoryClass: NovalnetTransactionsRepository::class)]
#[ORM\Table(name:'novalnet_transactions')]
class NovalnetTransactions implements NovalnetTransactionsInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $payment_id = null;

    #[ORM\Column]
    private ?int $order_id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $tid = null;

    #[ORM\Column(length: 50)]
    private ?string $payment_type = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?int $paid_amount = null;

    #[ORM\Column]
    private ?int $refund_amount = null;

    #[ORM\Column(length: 50)]
    private ?string $gateway_status = null;

    #[ORM\Column(nullable: true)]
    private array $details = [];

    #[ORM\Column(length: 10)]
    private ?string $currency = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentId(): ?int
    {
        return $this->payment_id;
    }

    public function setPaymentId(int $payment_id): static
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): static
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function setTid(string $tid): static
    {
        $this->tid = $tid;

        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->payment_type;
    }

    public function setPaymentType(string $payment_type): static
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaidAmount(): ?int
    {
        return $this->paid_amount;
    }

    public function setPaidAmount(int $paid_amount): static
    {
        $this->paid_amount = $paid_amount;

        return $this;
    }

    public function getRefundAmount(): ?int
    {
        return $this->refund_amount;
    }

    public function setRefundAmount(int $refund_amount): static
    {
        $this->refund_amount = $refund_amount;

        return $this;
    }

    public function getGatewayStatus(): ?string
    {
        return $this->gateway_status;
    }

    public function setGatewayStatus(string $gateway_status): static
    {
        $this->gateway_status = $gateway_status;

        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }
}
