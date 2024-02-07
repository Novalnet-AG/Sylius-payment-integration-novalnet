<?php

declare(strict_types=1);

namespace spec\Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Action\ResolveNextRouteAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetPaymentGatewayFactory;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class ResolveNextRouteActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResolveNextRouteAction::class);
    }

    function it_executes_resolve_next_route_request_with_completed_payment(
        ResolveNextRoute $request,
        PaymentInterface $payment,
        OrderInterface $order,
    ) {
        $request->getFirstModel()->willReturn($payment);
        $payment->getOrder()->willReturn($order);
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);
        $request->setRouteName('sylius_shop_order_thank_you')->shouldBeCalled();
        $this->execute($request);
    }

    function it_executes_resolve_next_route_request_with_authorized_payment(
        ResolveNextRoute $request,
        PaymentInterface $payment,
        OrderInterface $order,
    ) {
        $request->getFirstModel()->willReturn($payment);
        $payment->getOrder()->willReturn($order);
        $payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);
        $request->setRouteName('sylius_shop_order_thank_you')->shouldBeCalled();
        $this->execute($request);
    }

    function it_executes_resolve_next_route_request_with_processing_payment(
        ResolveNextRoute $request,
        PaymentInterface $payment,
        OrderInterface $order,
    ) {
        $request->getFirstModel()->willReturn($payment);
        $payment->getOrder()->willReturn($order);
        $payment->getState()->willReturn(PaymentInterface::STATE_PROCESSING);
        $request->setRouteName('sylius_shop_order_thank_you')->shouldBeCalled();
        $this->execute($request);
    }

    function it_executes_resolve_next_route_request_with_other_payment_state(
        ResolveNextRoute $request,
        PaymentInterface $payment,
        OrderInterface $order,
    ) {
        $request->getFirstModel()->willReturn($payment);
        $payment->getOrder()->willReturn($order);
        $payment->getState()->willReturn(PaymentInterface::STATE_CANCELLED);
        $order->getTokenValue()->willReturn('test_token');
        $request->setRouteName('sylius_shop_order_show')->shouldBeCalled();
        $request->setRouteParameters(['tokenValue' => 'test_token'])->shouldBeCalled();
        $this->execute($request);
    }

    function it_supports_resolve_next_route_request_with_payment_as_first_model(
        ResolveNextRoute $request,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn(NovalnetPaymentGatewayFactory::FACTORY_NAME);

        $this->supports($request)->shouldReturn(true);
    }
}
