<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class NovalnetCredentials extends Constraint
{
    public function validatedBy(): string
    {
        return 'novalnet_sylius_novalnet_plugin_credentials';
    }
}
