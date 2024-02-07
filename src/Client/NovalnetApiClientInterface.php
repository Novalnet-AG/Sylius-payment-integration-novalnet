<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Client;

interface NovalnetApiClientInterface
{
    public const API_BASE_URL = 'https://payport.novalnet.de/v2/';

    public const API_STATUS_SUCCESS = 'SUCCESS';

    public const API_STATUS_FAILURE = 'FAILURE';

    public const STATUS_CONFIRMED = 'CONFIRMED';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_ON_HOLD = 'ON_HOLD';

    public const STATUS_DEACTIVATED = 'DEACTIVATED';

    public const STATUS_FAILURE = 'FAILURE';

    public function createSeamlessPaymentLink($paymentData): array;

    public function getMerchantDetails($merchantData): array;

    public function getTransactionDetails($transactionData): array;

    public function createRefund($refundData): array;

    public function captureTransaction($captureData): array;

    public function cancelTransaction($captureData): array;

    public function getApiSignature(): string;

    public function getPaymentTariff(): string;

    public function getApiMode(): int;

    public function verifyChecksum($query, $txnSecret): bool;

    public function isSuccessApi($response): bool;

    public function isSuccessReturn($queryArgs): bool;

    public function getRedirectReturnStatusText($queryArgs, string $default = 'Unknown error occured'): string;

    public function getApiResponseStatusText($response, string $default = 'Unknown API error occured'): string;
}
