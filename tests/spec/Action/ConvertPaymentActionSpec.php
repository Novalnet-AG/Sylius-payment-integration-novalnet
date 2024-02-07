<?php

declare(strict_types=1);

namespace spec\Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Action\ConvertPaymentAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCodeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Convert;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class ConvertPaymentActionSpec extends ObjectBehavior
{
    function let(CustomLangCodeInterface $customLangCode): void
    {
        $this->beConstructedWith($customLangCode);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_execute(
        Convert $request,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        CustomLangCodeInterface $customLangCode,
        ArrayObject $details,
    ): void {
        $customer->getGender()->willReturn('u');
        $customer->getEmail()->willReturn('test@novalnet.de');
        $customer->getId()->willReturn(1);
        $order->getCustomerIp()->willReturn('127.0.0.1');
        $order->getLocaleCode()->willReturn('en_US');

        $billingAddress->getFirstName()->willReturn('Norbert');
        $billingAddress->getLastName()->willReturn('Maier');
        $billingAddress->getStreet()->willReturn('Musterstr');
        $billingAddress->getCity()->willReturn('Musterhausen');
        $billingAddress->getPostcode()->willReturn('12345');
        $billingAddress->getCountryCode()->willReturn('DE');
        $billingAddress->getCompany()->willReturn(null);

        $shippingAddress->getStreet()->willReturn('Hauptstr');
        $shippingAddress->getCity()->willReturn('Kaiserslautern');
        $shippingAddress->getPostcode()->willReturn('66862');
        $shippingAddress->getCountryCode()->willReturn('DE');

        $payment->getDetails()->willReturn([]);
        $payment->getAmount()->willReturn(100);
        $payment->getCurrencyCode()->willReturn('EUR');
        $payment->getId()->willReturn(70);

        $order->getCustomer()->willReturn($customer);
        $order->getLocaleCode()->willReturn('en_US');
        $order->getShippingAddress()->willReturn($shippingAddress);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->getNumber()->willReturn(100);

        $payment->getOrder()->willReturn($order);

        $customLangCode->getLangCode('en_US')->willReturn('EN');

        $details = ArrayObject::ensureArrayObject([]);

        $requestData = [
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
        ];

        $details->offsetSet('requestData', $requestData);

        $details->offsetSet(
            'paymentData',
            [
                'transaction' => [
                    'amount' => 100,
                    'currency' => 'EUR',
                    'order_no' => 100,
                ],
            ],
        );

        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');
        $request->setResult((array) $details)->shouldBeCalled();
        $this->execute($request);
    }

    function it_supports_only_convert_request_payment_source_and_array_to(
        Convert $request,
        PaymentInterface $payment,
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
