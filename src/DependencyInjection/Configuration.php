<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('novalnet_sylius_novalnet_payment_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
