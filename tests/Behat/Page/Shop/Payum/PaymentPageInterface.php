<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Shop\Payum;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface PaymentPageInterface extends PageInterface
{
    public function pay(): void;

    public function cancel(): void;
}
