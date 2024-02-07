<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Client;

use ArrayObject;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class NovalnetApiClient implements NovalnetApiClientInterface
{
    public const API_BASE_URL = 'https://payport.novalnet.de/v2/';

    public const API_STATUS_SUCCESS = 'SUCCESS';

    public const API_STATUS_FAILURE = 'FAILURE';

    public const STATUS_CONFIRMED = 'CONFIRMED';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_ON_HOLD = 'ON_HOLD';

    public const STATUS_DEACTIVATED = 'DEACTIVATED';

    public const STATUS_FAILURE = 'FAILURE';

    /** @var string */
    private $apiSignature;

    /** @var string */
    private $apiAccessKey;

    /** @var string */
    private $paymentTariff;

    /** @var HttpClinet */
    private $httpClient;

    /** @var bool */
    private $useAuthorize;

    /** @var bool */
    private $apiTestMode;

    public function __construct(ArrayObject $config)
    {
        $this->apiSignature = $config['api_signature'];
        $this->apiAccessKey = $config['api_access_key'];
        $this->paymentTariff = $config['payment_tariff'];
        $this->useAuthorize = $config['use_authorize'];
        $this->apiTestMode = $config['test_mode'];
        $this->httpClient = new HttpClient();
    }

    public function createSeamlessPaymentLink($paymentData): array
    {
        if ($this->useAuthorize === true) {
            return $this->sendHttpRequest($paymentData, 'seamless/authorize');
        }

        return $this->sendHttpRequest($paymentData, 'seamless/payment');
    }

    public function getMerchantDetails($merchantData): array
    {
        return $this->sendHttpRequest($merchantData, 'merchant/details');
    }

    public function getTransactionDetails($transactionData): array
    {
        return $this->sendHttpRequest($transactionData, 'transaction/details');
    }

    public function createRefund($refundData): array
    {
        return $this->sendHttpRequest($refundData, 'transaction/refund');
    }

    public function captureTransaction($captureData): array
    {
        return $this->sendHttpRequest($captureData, 'transaction/capture');
    }

    public function cancelTransaction($captureData): array
    {
        return $this->sendHttpRequest($captureData, 'transaction/cancel');
    }

    public function getApiSignature(): string
    {
        return $this->apiSignature;
    }

    private function getApiAccessKey(): string
    {
        return $this->apiAccessKey;
    }

    public function getPaymentTariff(): string
    {
        return (string) $this->paymentTariff;
    }

    public function getApiMode(): int
    {
        return ($this->apiTestMode === true) ? 1 : 0;
    }

    public function verifyChecksum($query, $txnSecret): bool
    {
        $tokenString = '';
        $tokenString .= $query['tid'];
        $tokenString .= $txnSecret;
        $tokenString .= $query['status'];
        $tokenString .= strrev($this->getApiAccessKey());
        $generatedChecksum = hash('sha256', $tokenString);

        return $generatedChecksum === $query['checksum'];
    }

    public function isSuccessApi($response): bool
    {
        return isset($response['result']['status']) && self::API_STATUS_SUCCESS === $response['result']['status'];
    }

    public function isSuccessReturn($queryArgs): bool
    {
        return isset($queryArgs['status']) && self::API_STATUS_SUCCESS === $queryArgs['status'];
    }

    public function getRedirectReturnStatusText($queryArgs, string $default = 'Unknown error occured'): string
    {
        return $queryArgs['status_text'] ?? $default;
    }

    public function getApiResponseStatusText($response, string $default = 'Unknown API error occured'): string
    {
        return $response['result']['status_text'] ?? $default;
    }

    private function sendHttpRequest($data, $apiEndPoint): array
    {
        try {
            $httpResponse = $this->httpClient->request('POST', $this->getRequestUri($apiEndPoint), [
                'body' => json_encode($data),
                'headers' => $this->getRequestHeader(),
            ]);

            return json_decode($httpResponse->getBody()->getContents(), true);
        } catch(GuzzleException $e) {
            return [
                'result' => [
                    'status_code' => '106',
                    'status' => self::API_STATUS_FAILURE,
                    'status_text' => $e->getMessage(),
                ],
            ];
        }
    }

    private function getRequestUri($apiEndPoint = null)
    {
        return self::API_BASE_URL . trim($apiEndPoint);
    }

    private function getRequestHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'Charset' => 'utf-8',
            'Accept' => 'application/json',
            'X-NN-Access-Key' => base64_encode($this->getApiAccessKey()),
        ];
    }
}
