<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NovalnetSyliusNovalnetPaymentPlugin extends Bundle
{
    use SyliusPluginTrait;

    public const NOVALNET_VERSION = '1.0.0';

    public const SYSTEM_VERSION = SyliusCoreBundle::VERSION;
}
