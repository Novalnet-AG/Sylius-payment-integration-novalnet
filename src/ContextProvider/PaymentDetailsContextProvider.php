<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\ContextProvider;

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class PaymentDetailsContextProvider implements ContextProviderInterface
{
    public function __construct(
        private RepositoryInterface $transactions,
    ) {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        if (!isset($templateContext['order'])) {
            return $templateContext;
        }

        /** @var OrderInterface $order */
        $order = $templateContext['order'];

        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->last();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        if ('novalnet_payment' === $paymentMethod->getGatewayConfig()->getFactoryName()) {
            $paymentDetails = $payment->getDetails();
            if (isset($paymentDetails['transactionData'])) {
                $templateContext['novalnetTxnData'] = $paymentDetails['transactionData'];
            }
            $templateContext['novalnetTransaction'] = $this->transactions->findOneBy(['payment_id' => $payment->getId()]);
        }

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return ('sylius.admin.order.show.payments_content' === $templateBlock->getEventName() || 'sylius.shop.account.order.show.subcontent' === $templateBlock->getEventName()) &&
            'payment_details' === $templateBlock->getName();
    }
}
