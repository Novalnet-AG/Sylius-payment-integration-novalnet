<?php

declare(strict_types=1);

namespace spec\Novalnet\SyliusNovalnetPaymentPlugin\Action;

use ArrayAccess;
use Novalnet\SyliusNovalnetPaymentPlugin\Action\AuthorizeAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CreatePaymentLink;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthorizeActionSpec extends ObjectBehavior
{
    function let(
        UrlGeneratorInterface $urlGenerator,
    ): void {
        $this->beConstructedWith($urlGenerator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AuthorizeAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_execute(
        Authorize $request,
        TokenInterface $token,
        UrlGeneratorInterface $urlGenerator,
        GatewayInterface $gateway,
        ArrayObject $details,
    ): void {
        $this->setGateway($gateway);

        $details = new ArrayObject([
            'requestData' => [
                'customer' => [
                    'gender' => 'u',
                    'first_name' => 'Norbert',
                    'last_name' => 'Maier',
                    'email' => 'test@novalnet.de',
                    'customer_ip' => '127.0.0.1',
                    'customer_no' => 1,
                    'billing' => [
                        'street' => 'Musterstr',
                        'city' => 'Musterhausen',
                        'zip' => '12345',
                        'country_code' => 'DE',
                    ],
                    'shipping' => [
                        'street' => 'Hauptstr',
                        'city' => 'Kaiserslautern',
                        'zip' => '66862',
                        'country_code' => 'DE',
                    ],
                ],
                'custom' => [
                    'lang' => 'EN',
                    'input1' => 'payment_id',
                    'inputval1' => 70,
                ],
            ],
            'paymentData' => [
                'transaction' => [
                    'amount' => 100,
                    'currency' => 'EUR',
                    'order_no' => 100,
                ],
            ],
        ]);

        $request->getModel()->willReturn($details);

        $token->getTargetUrl()->willReturn('test_url');
        $token->getGatewayName()->willReturn('payment_test');
        $request->getToken()->willReturn($token);

        $urlGenerator->generate(
            'novalnet_sylius_webhook_notifications',
            ['code' => 'payment_test'],
            UrlGeneratorInterface::ABSOLUTE_URL,
        )->willReturn('test_url_webhook');

        $paymentData = $details->offsetGet('paymentData');

        $paymentData['transaction']['return_url'] = 'test_url';
        $paymentData['transaction']['error_return_url'] = 'test_url';
        $paymentData['transaction']['hook_url'] = 'test_url_webhook';

        $details->offsetSet('paymentData', $paymentData);

        $gateway->execute(new CreatePaymentLink($details))->shouldBeCalledOnce();

        $this->execute($request);
    }

    function it_supports_only_capture_request_and_array_access(
        Authorize $request,
        ArrayAccess $arrayAccess,
    ): void {
        $request->getModel()->willReturn($arrayAccess);
        $this->supports($request)->shouldReturn(true);
    }
}
