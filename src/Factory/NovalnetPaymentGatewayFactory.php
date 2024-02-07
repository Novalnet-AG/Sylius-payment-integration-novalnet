<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Factory;

use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClient;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class NovalnetPaymentGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'novalnet_payment';

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'Novalnet Payment',
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new NovalnetApiClient($config);
        };
    }
}
