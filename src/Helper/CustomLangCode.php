<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Helper;

use Sylius\Component\Locale\Context\LocaleContextInterface;

final class CustomLangCode implements CustomLangCodeInterface
{
    public function __construct(
        private LocaleContextInterface $localeContext,
    ) {
    }

    public function getLangCode(string $localeCode = null): string
    {
        $localeCode = ($localeCode === null) ? $this->localeContext->getLocaleCode() : $localeCode;
        $landData = explode('_', $localeCode);

        return strtoupper(array_shift($landData));
    }
}
