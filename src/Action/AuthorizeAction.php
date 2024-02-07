<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Novalnet\SyliusNovalnetPaymentPlugin\Request\Api\CreatePaymentLink;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class AuthorizeAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($details['txnSecret'])) {
            return;
        }

        Assert::notEmpty($details['paymentData']);

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $paymentData = $details['paymentData'];
        $paymentData['transaction']['return_url'] = $token->getTargetUrl();
        $paymentData['transaction']['error_return_url'] = $token->getTargetUrl();
        $paymentData['hosted_page']['hide_payments'] = [
            'PREPAYMENT',
        ];
        $paymentData['transaction']['hook_url'] = $this->urlGenerator->generate(
            'novalnet_sylius_webhook_notifications',
            ['code' => $token->getGatewayName()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $details['paymentData'] = $paymentData;

        $this->gateway->execute(new CreatePaymentLink($details));
    }

    public function supports($request): bool
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
