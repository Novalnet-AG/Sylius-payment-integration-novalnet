<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetPaymentGatewayFactory;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;

final class ManagingPaymentMethodNovalnetContext implements Context
{
    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var CreatePageInterface */
    private $createPage;

    public function __construct(
        CurrentPageResolverInterface $currentPageResolver,
        CreatePageInterface $createPage,
    ) {
        $this->createPage = $createPage;
        $this->currentPageResolver = $currentPageResolver;
    }

    /**
     * @Given I want to create a new Novalnet payment method
     */
    public function iWantToCreateANewNovalnetPaymentMethod()
    {
        $this->createPage->open(['factory' => NovalnetPaymentGatewayFactory::FACTORY_NAME]);
    }

    /**
     * @When I configure it with test Novalnet credentials
     */
    public function iConfigureItWithTestNovalnetCredentials()
    {
        // Replace value with Test credentials. You will find the Product activation key in the Novalnet Admin Portal: Projects > Choose your project > API credentials > API Signature (Product activation key)
        $this->resolveCurrentPage()->setApiSignature('Product activation key');
        // Replace value with Test credentials. Get your Payment access key from the Novalnet Admin Portal: Projects > Choose your project > API credentials > Payment access key
        $this->resolveCurrentPage()->setApiAccessKey('Payment access key');
    }

    /**
     * @return CreatePageInterface|SymfonyPageInterface
     */
    private function resolveCurrentPage(): SymfonyPageInterface
    {
        return $this->currentPageResolver->getCurrentPageWithForm([
            $this->createPage,
        ]);
    }
}
