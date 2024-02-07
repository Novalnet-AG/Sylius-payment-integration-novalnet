<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setApiSignature(string $environment): void
    {
        $this->getElement('api_signature')->setValue($environment);
    }

    public function setApiAccessKey(string $environment): void
    {
        $this->getElement('api_access_key')->setValue($environment);
    }

    protected function getDefinedElements(): array
    {
        return parent::getDefinedElements() + [
            'api_signature' => '#sylius_payment_method_gatewayConfig_config_api_signature',
            'api_access_key' => '#sylius_payment_method_gatewayConfig_config_api_access_key',
        ];
    }
}
