<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action\Api;

use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CreatePaymentLink;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class CreatePaymentLinkAction extends ApiAwareAction implements ActionInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function execute($request): void
    {
        /** @var array $details */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $paymentLinkData = [
            'merchant' => [
                'signature' => $this->novalnetApiClient->getApiSignature(),
                'tariff' => $this->novalnetApiClient->getPaymentTariff(),
            ],
            'transaction' => [
                'test_mode' => $this->novalnetApiClient->getApiMode(),
            ],
            'hosted_page' => [
                'hide_blocks' => ['ADDRESS_FORM', 'TARIFF', 'LANGUAGE_MENU', 'SHOP_INFO'],
                'skip_pages' => ['CONFIRMATION_PAGE'],
            ],
        ];

        $paymentLinkData = array_merge_recursive($paymentLinkData, $details['requestData'], $details['paymentData']);

        $paymentLink = $this->novalnetApiClient->createSeamlessPaymentLink($paymentLinkData);

        if ($this->novalnetApiClient->isSuccessApi($paymentLink)) {
            $details['txnSecret'] = $paymentLink['transaction']['txn_secret'];

            throw new HttpRedirect($paymentLink['result']['redirect_url']);
        }
        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->requestStack->getSession()->getBag('flashes');
        $flashBag->add('error', $this->novalnetApiClient->getApiResponseStatusText($paymentLink));
        $details['txnStatus'] = $paymentLink['result']['status'];

        throw new HttpRedirect($paymentLinkData['transaction']['error_return_url']);
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreatePaymentLink &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
