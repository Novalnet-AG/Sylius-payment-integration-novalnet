<?php

declare(strict_types=1);

namespace spec\Novalnet\SyliusNovalnetPaymentPlugin\Action;

use Iterator;
use Novalnet\SyliusNovalnetPaymentPlugin\Action\StatusAction;
use Novalnet\SyliusNovalnetPaymentPlugin\Client\NovalnetApiClient;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\CustomLangCodeInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Helper\TransactionNotesInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Sender\PaymentDetailsEmailSenderInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionHistoryActionInterface;
use Novalnet\SyliusNovalnetPaymentPlugin\Transaction\NovalnetTransactionsActionInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetStatusInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StatusActionSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        PaymentDetailsEmailSenderInterface $paymentDetailsEmailSender,
        NovalnetTransactionHistoryActionInterface $transactionHistory,
        NovalnetTransactionsActionInterface $transaction,
        TransactionNotesInterface $transactionNotes,
        CustomLangCodeInterface $customLangCode,
        NovalnetApiClient $novalnetApiClient,
        GatewayInterface $gateway,
    ): void {
        $this->beConstructedWith(
            $requestStack,
            $paymentDetailsEmailSender,
            $transactionHistory,
            $transaction,
            $transactionNotes,
            $customLangCode,
        );

        $this->setApi($novalnetApiClient);
        $this->setGateway($gateway);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StatusAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_marks_as_new_when_status_is_new(
        GetStatusInterface $request,
        ArrayObject $model,
        Iterator $iterator,
        PaymentInterface $payment,
    ): void {
        $details = [];
        $model->getIterator()->willReturn($iterator);
        $request->getModel()->willReturn($payment);
        $payment->getDetails()->willReturn($details);
        if (!isset($details['txnStatus']) && !isset($details['txnSecret'])) {
            $request->markNew()->shouldBeCalled();
            $this->execute($request);
        }
    }

    function it_supports_only_get_status_request_and_array_access(
        GetStatusInterface $request,
        PaymentInterface $payment,
    ): void {
        $request->getModel()->willReturn($payment);
        $this->supports($request)->shouldReturn(true);
    }
}
