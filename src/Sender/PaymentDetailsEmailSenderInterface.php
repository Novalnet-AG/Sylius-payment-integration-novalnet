<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Sender;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDetailsEmailSenderInterface
{
    public function sendConfirmationEmail(PaymentInterface $payment): void;
}
