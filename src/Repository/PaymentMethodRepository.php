<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Repository;

use Doctrine\ORM\NoResultException;
use Novalnet\SyliusNovalnetPaymentPlugin\Factory\NovalnetPaymentGatewayFactory;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository as BasePaymentMethodRepository;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class PaymentMethodRepository extends BasePaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function getOneForPaymentCode(string $code): PaymentMethodInterface
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.gatewayConfig', 'gatewayConfig')
            ->where('gatewayConfig.factoryName = :factoryName')
            ->andWhere('o.code = :code')
            ->setParameter('factoryName', NovalnetPaymentGatewayFactory::FACTORY_NAME)
            ->setParameter('code', $code)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findOneForPaymentCode(string $code): ?PaymentMethodInterface
    {
        try {
            return $this->getOneForPaymentCode($code);
        } catch (NoResultException $exception) {
            return null;
        }
    }
}
