<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Helper;

interface CustomLangCodeInterface
{
    public function getLangCode(string $localeCode = null): string;
}
