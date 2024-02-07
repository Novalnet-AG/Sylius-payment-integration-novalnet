<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Sender;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class PaymentDetailsEmailSender implements PaymentDetailsEmailSenderInterface
{
    public function __construct(
        private SenderInterface $emailSender,
        private RepositoryInterface $transactions,
    ) {
    }

    public function sendConfirmationEmail(PaymentInterface $payment): void
    {
        if (0 === count($payment->getDetails())) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $this->emailSender->send('payment_details', [$customer->getEmail()], [
            'novalnetTransaction' => $this->transactions->findOneBy(['payment_id' => $payment->getId()]),
            'order' => $order,
            'channel' => $order->getChannel(),
            'localeCode' => $order->getLocaleCode(),
        ]);
    }
}
