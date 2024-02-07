<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\StateMachine;

use SM\Event\TransitionEvent;
use Sylius\Component\Core\Model\PaymentInterface;

interface ExtensionProcessorInterface
{
    public function process(PaymentInterface $payment, TransitionEvent $event): void;
}
