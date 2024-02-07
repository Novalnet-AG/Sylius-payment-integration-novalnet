<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Controller;

use Novalnet\SyliusNovalnetPaymentPlugin\Repository\PaymentMethodRepositoryInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class NotifyController
{
    /** @var array */
    private $paymentGatewayConfig;

    /** @var array */
    private $eventData;

    /** @var string */
    private $novalnetHostName = 'pay-nn.de';

    /** @var array */
    private $mandatoryEventData = [
        'event' => [
            'type',
            'checksum',
            'tid',
        ],
        'merchant' => [
            'vendor',
            'project',
        ],
        'result' => [
            'status',
        ],
        'transaction' => [
            'tid',
            'payment_type',
            'status',
        ],
    ];

    public function __construct(
        private Payum $payum,
        private PaymentMethodRepositoryInterface $paymentMethodRespository,
    ) {
    }

    /**
     * @throws ReplyInterface
     */
    public function doAction(Request $request): Response
    {
        try {
            $paymentMethodCode = $request->attributes->get('code');
            if (!is_string($paymentMethodCode) || '' === $paymentMethodCode) {
                throw new InvalidArgumentException('Invalid Payment code');
            }
            $paymentMethod = $this->paymentMethodRespository->findOneForPaymentCode($paymentMethodCode);

            if (null === $paymentMethod) {
                throw new NotFoundHttpException('Payment method not found');
            }
            $this->paymentGatewayConfig = $paymentMethod->getGatewayConfig()->getConfig();
            $this->authenticationNotifyClient($request);
            $this->validateEventData($request);
            $this->authenticationNotifyEventData($request);
            $gateway = $this->payum->getGateway($paymentMethod->getCode());
            $eventData = ArrayObject::ensureArrayObject($this->eventData);
            $gateway->execute(new Notify($eventData));
        } catch(AccessDeniedHttpException | NotFoundHttpException | BadRequestHttpException | UnauthorizedHttpException $e) {
            return new Response($e->getMessage(), $e->getStatusCode());
        } catch(InvalidArgumentException $e) {
            return new Response($e->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
        } catch(HttpException $e) {
            return new Response($e->getMessage());
        }
    }

    private function validateEventData(Request $request): void
    {
        $this->eventData = json_decode($request->getContent(), true);

        // Validate request parameters.
        foreach ($this->mandatoryEventData as $category => $parameters) {
            if (empty($this->eventData[$category])) {
                throw new NotFoundHttpException(sprintf('Required parameter category(%s) not received', $category));
            }
            if (!empty($parameters)) {
                foreach ($parameters as $parameter) {
                    if (empty($this->eventData[$category][$parameter])) {
                        throw new NotFoundHttpException(sprintf('Required parameter(%1$s) in the category(%2$s) not received', $parameter, $category));
                    }
                    if (in_array($parameter, ['tid', 'parent_tid'], true) && !preg_match('/^\d{17}$/', (string) $this->eventData[$category][$parameter])) {
                        throw new BadRequestHttpException(sprintf('Invalid TID[%1$s] received in the category(%2$s).', $parameter, $category));
                    }
                }
            }
        }
    }

    private function authenticationNotifyClient(Request $request): void
    {
        $isAllowedForTestNotification = (isset($this->paymentGatewayConfig['allow_test_notification']) && true === $this->paymentGatewayConfig['allow_test_notification']) ? true : false;

        // Host based validation.
        if (!empty($this->novalnetHostName)) {
            $novalnetHostIp = gethostbyname($this->novalnetHostName);
            // Authenticating the server request based on IP.
            $requestClientIp = $request->getClientIp();
            if (!empty($novalnetHostIp) && !empty($requestClientIp)) {
                if ($novalnetHostIp !== $requestClientIp && empty($isAllowedForTestNotification)) {
                    throw new AccessDeniedHttpException(sprintf('Unauthorised access from the IP %s', $requestClientIp));
                }
            } else {
                throw new AccessDeniedHttpException('Unauthorised access from the IP. Host/recieved IP is empty');
            }
        } else {
            throw new AccessDeniedHttpException('Unauthorised access from the IP. Host/recieved IP is empty');
        }
    }

    private function authenticationNotifyEventData(Request $request): void
    {
        $paymentAccessKey = (!empty($this->paymentGatewayConfig['api_access_key'])) ? $this->paymentGatewayConfig['api_access_key'] : '';

        $token_string = $this->eventData['event']['tid'] . $this->eventData['event']['type'] . $this->eventData['result']['status'];

        if (isset($this->eventData['transaction']['amount'])) {
            $token_string .= $this->eventData['transaction']['amount'];
        }
        if (isset($this->eventData['transaction']['currency'])) {
            $token_string .= $this->eventData['transaction']['currency'];
        }
        if (!empty($paymentAccessKey)) {
            $token_string .= strrev($paymentAccessKey);
        }

        $generated_checksum = hash('sha256', $token_string);

        if ($generated_checksum !== $this->eventData['event']['checksum']) {
            throw new UnauthorizedHttpException('checksum', 'While notifying some data has been changed. The hash check failed');
        }

        if (!empty($this->eventData['custom']['shop_invoked'])) {
            throw new HttpException(Response::HTTP_OK, 'Process already handled in the shop.');
        }
    }
}
