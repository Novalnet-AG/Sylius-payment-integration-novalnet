<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Validator\Constraint;

use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClient;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCode;
use Payum\Core\Bridge\Spl\ArrayObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class NovalnetCredentialsValidator extends ConstraintValidator
{
    public function __construct(
        private CustomLangCode $customLangCode,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, NovalnetCredentials::class);
        Assert::isArray($value);
        $paymentConfigurations = ArrayObject::ensureArrayObject($value);
        if ($paymentConfigurations->validateNotEmpty(['api_signature', 'api_access_key'], false)) {
            $novalnetApiClient = new NovalnetApiClient($paymentConfigurations);
            $merchantDetails = $novalnetApiClient->getMerchantDetails([
                'merchant' => [
                    'signature' => $paymentConfigurations->offsetGet('api_signature'),
                ],
                'custom' => [
                    'lang' => $this->customLangCode->getLangCode(),
                ],
            ]);

            if (!$novalnetApiClient->isSuccessApi($merchantDetails)) {
                $statusText = $novalnetApiClient->getApiResponseStatusText($merchantDetails);
                $this->context->buildViolation($statusText)->addViolation();
            }
        }
    }
}
