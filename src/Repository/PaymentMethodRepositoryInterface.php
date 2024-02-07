<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Repository;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface as BasePaymentMethodRepositoryInterface;

interface PaymentMethodRepositoryInterface extends BasePaymentMethodRepositoryInterface
{
    public function getOneForPaymentCode(string $code): PaymentMethodInterface;

    public function findOneForPaymentCode(string $code): ?PaymentMethodInterface;
}
