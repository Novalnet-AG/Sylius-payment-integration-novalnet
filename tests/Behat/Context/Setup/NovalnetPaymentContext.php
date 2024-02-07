<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetPaymentGatewayFactory;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

final class NovalnetPaymentContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ExampleFactoryInterface */
    private $paymentMethodExampleFactory;

    /** @var EntityManagerInterface */
    private $paymentMethodManager;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ExampleFactoryInterface $paymentMethodExampleFactory,
        EntityManagerInterface $paymentMethodManager,
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
        $this->paymentMethodManager = $paymentMethodManager;
    }

    /**
     * @Given the store has a payment method :arg1 with a code :arg2 and Novalnet gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndNovalnetGateway(
        string $paymentMethodName,
        string $paymentMethodCode,
    ) {
        $paymentMethod = $this->createPaymentMethodNovalnetPayment(
            $paymentMethodName,
            $paymentMethodCode,
            NovalnetPaymentGatewayFactory::FACTORY_NAME,
            'Novalnet AG Payment Gateway',
        );

        $paymentMethod->getGatewayConfig()->setConfig([
            'api_signature' => 'Production Activation Key', // Replace value with Test credentials. You will find the Product activation key in the Novalnet Admin Portal: Projects > Choose your project > API credentials > API Signature (Product activation key)
            'api_access_key' => 'Payment access key', // Replace value with Test credentials. Get your Payment access key from the Novalnet Admin Portal: Projects > Choose your project > API credentials > Payment access key
            'payment_tariff' => 'Novalnet Tariff', // Replace value with Test tariff.
            'use_authorize' => false,
            'test_mode' => true,
        ]);

        $this->paymentMethodManager->flush();
    }

    private function createPaymentMethodNovalnetPayment(
        string $name,
        string $code,
        string $factoryName,
        string $description = '',
        bool $addForCurrentChannel = true,
        int $position = null,
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $factoryName,
            'gatewayFactory' => $factoryName,
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
