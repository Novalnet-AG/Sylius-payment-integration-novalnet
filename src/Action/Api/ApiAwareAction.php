<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Action\Api;

use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClient;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class ApiAwareAction implements ApiAwareInterface
{
    /** @var NovalnetApiClient */
    protected $novalnetApiClient;

    public function setApi($api): void
    {
        if (false === $api instanceof NovalnetApiClient) {
            throw new UnsupportedApiException('Not supported.Expected an instance of ' . NovalnetApiClient::class);
        }

        $this->novalnetApiClient = $api;
    }
}
