<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novalnet\SyliusNovalnetPaymentPlugin\Repository\NovalnetTransactionHistoryRepository;

#[ORM\Entity(repositoryClass: NovalnetTransactionHistoryRepository::class)]
#[ORM\Table(name:'novalnet_transaction_history')]
class NovalnetTransactionHistory implements NovalnetTransactionHistoryInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $order_id = null;

    #[ORM\Column]
    private ?int $payment_id = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(nullable: true)]
    private ?bool $private = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPaymentId(): ?int
    {
        return $this->payment_id;
    }

    public function setPaymentId(int $payment_id): static
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
