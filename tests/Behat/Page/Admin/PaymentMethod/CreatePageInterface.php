<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setApiSignature(string $environment): void;

    public function setApiAccessKey(string $environment): void;
}
