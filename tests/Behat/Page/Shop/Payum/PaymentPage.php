<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Shop\Payum;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfBehat\PageObjectExtension\Page\Page;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClientInterface;
use Payum\Core\Security\TokenInterface;
use RuntimeException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\BrowserKit\AbstractBrowser as Client;

final class PaymentPage extends Page implements PaymentPageInterface
{
    /** @var RepositoryInterface */
    private $securityTokenRepository;

    /** @var EntityRepository */
    private $paymentRepository;

    /** @var Client */
    private $client;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        Session $session,
        MinkParameters $parameters,
        RepositoryInterface $securityTokenRepository,
        EntityRepository $paymentRepository,
        EntityManagerInterface $entityManager,
        Client $client,
    ) {
        parent::__construct($session, $parameters);

        $this->paymentRepository = $paymentRepository;
        $this->securityTokenRepository = $securityTokenRepository;
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function pay(): void
    {
        $token = $this->findToken();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($token->getDetails()->getId());
        $paymentDetails = $payment->getDetails();
        $paymentDetails['txnStatus'] = NovalnetApiClientInterface::STATUS_CONFIRMED;
        $paymentDetails['txnSecret'] = 'Test';
        $payment->setDetails($paymentDetails);
        $this->entityManager->merge($payment);
        $this->entityManager->flush();

        $this->getDriver()->visit($token->getTargetUrl());
    }

    /**
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function cancel(): void
    {
        $token = $this->findToken();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($token->getDetails()->getId());
        $paymentDetails = $payment->getDetails();
        $paymentDetails['txnStatus'] = NovalnetApiClientInterface::STATUS_FAILURE;
        $paymentDetails['txnSecret'] = 'Test';
        $payment->setDetails($paymentDetails);
        $this->entityManager->merge($payment);
        $this->entityManager->flush();

        $this->getDriver()->visit($token->getTargetUrl());
    }

    protected function getUrl(array $urlParameters = []): string
    {
        return NovalnetApiClientInterface::API_BASE_URL;
    }

    private function findToken(string $type = 'capture'): TokenInterface
    {
        $tokens = $this->securityTokenRepository->findAll();

        /** @var TokenInterface $token */
        foreach ($tokens as $token) {
            if (strpos($token->getTargetUrl(), $type)) {
                return $token;
            }
        }

        throw new RuntimeException('Cannot find capture token, check if you are after proper checkout steps');
    }
}
