<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\StateMachine;

use Novalnet\SyliusNovalnetPaymentPlugin\Context\Admin\AdminUserContextInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CancelTransaction;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CaptureTransaction;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\RefundTransaction;
use Payum\Core\Payum;
use SM\Event\TransitionEvent;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class ExtensionProcessor implements ExtensionProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private Payum $payum,
        private AdminUserContextInterface $adminUserContext,
    ) {
    }

    public function process(PaymentInterface $payment, TransitionEvent $event): void
    {
        $adminUser = $this->adminUserContext->getAdminUser();
        if (null === $adminUser) {
            return;
        }

        $details = $payment->getDetails();
        if (isset($details['txnSecret']) && '' !== $details['txnSecret']) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $payment->getMethod();

            try {
                if (null !== $paymentMethod->getCode()) {
                    $gateway = $this->payum->getGateway($paymentMethod->getCode());
                    if (PaymentInterface::STATE_AUTHORIZED === $event->getState() && PaymentTransitions::TRANSITION_COMPLETE === $event->getTransition()) {
                        $gateway->execute(new CaptureTransaction($payment));
                    } elseif (PaymentInterface::STATE_AUTHORIZED === $event->getState() && PaymentTransitions::TRANSITION_CANCEL === $event->getTransition()) {
                        $gateway->execute(new CancelTransaction($payment));
                    } elseif (PaymentTransitions::TRANSITION_REFUND === $event->getTransition()) {
                        $gateway->execute(new RefundTransaction($payment));
                    }
                }
            } catch(\Exception $e) {
                /** @var FlashBagInterface $flashBag */
                $flashBag = $this->requestStack->getSession()->getBag('flashes');
                $flashBag->add('error', $e->getMessage());

                throw new UpdateHandlingException();
            }
        }
    }
}
