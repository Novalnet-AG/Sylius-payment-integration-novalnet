<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCodeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentAction implements ActionInterface
{
    public function __construct(
        private CustomLangCodeInterface $customLangCode,
    ) {
    }

    /** @param Convert|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        /** @var AddressInterface $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        /** @var AddressInterface $billingAddress */
        $billingAddress = $order->getBillingAddress();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $requestData = [
            'customer' => [
                'gender' => $customer->getGender(),
                'first_name' => $billingAddress->getFirstName() ?? $customer->getFirstName(),
                'last_name' => $billingAddress->getLastName() ?? $customer->getLastName(),
                'email' => $customer->getEmail(),
                'customer_ip' => $order->getCustomerIp(),
                'customer_no' => $customer->getId(),
                'billing' => [
                    'street' => $billingAddress->getStreet(),
                    'city' => $billingAddress->getCity(),
                    'zip' => $billingAddress->getPostcode(),
                    'country_code' => $billingAddress->getCountryCode(),
                ],
                'shipping' => [
                    'street' => $shippingAddress->getStreet(),
                    'city' => $shippingAddress->getCity(),
                    'zip' => $shippingAddress->getPostcode(),
                    'country_code' => $shippingAddress->getCountryCode(),
                ],
            ],
            'custom' => [
                'lang' => $this->customLangCode->getLangCode($order->getLocaleCode()),
                'input1' => 'payment_id',
                'inputval1' => $payment->getId(),
            ],
        ];

        if ($billingAddress->getCompany() !== null) {
            $requestData['customer']['billing']['company'] = $billingAddress->getCompany();
        }

        $details->offsetSet('requestData', $requestData);

        $details->offsetSet(
            'paymentData',
            [
                'transaction' => [
                    'amount' => $payment->getAmount(),
                    'currency' => $payment->getCurrencyCode(),
                    'order_no' => $order->getNumber(),
                ],
            ],
        );

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' == $request->getTo()
        ;
    }
}
