<?php

declare(strict_types=1);

namespace Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Tests\Novalnet\SyliusNovalnetPaymentPlugin\Behat\Page\Shop\Payum\PaymentPageInterface;

final class NovalnetCheckoutContext implements Context
{
    public function __construct(
        private ShowPageInterface $orderDetails,
        private CompletePageInterface $summaryPage,
        private PaymentPageInterface $paymentPage,
    ) {
    }

    /**
     * @When I confirm my order with Novalnet payment
     */
    public function iConfirmMyOrderWithNovalnetPayment()
    {
        $this->summaryPage->confirmOrder();
    }

    /**
     * @When I redirect to Novalnet and pay successfully
     */
    public function iRedirectToNovalnetAndPaySuccessfully()
    {
        $this->paymentPage->pay();
    }

    /**
     * @When I redirect to Novalnet and cancel my Novalnet payment
     */
    public function iRedirectToNovalnetAndCancelMyNovalnetPayment()
    {
        $this->paymentPage->cancel();
    }

    /**
     * @When I try to pay again with Novalnet payment
     */
    public function iTryToPayAgainWithNovalnetPayment()
    {
        $this->orderDetails->pay();
    }

    /**
     * @Then I should be notified that my payment has been completed
     */
    public function iShouldBeNotifiedThatMyPaymentHasBeenCompleted()
    {
        $this->assertNotification('Payment has been completed.');
    }

    /**
     * @Then I should be notified that my payment has been falied
     */
    public function iShouldBeNotifiedThatMyPaymentHasBeenFalied()
    {
        $this->assertNotification('Payment has failed.');
    }

    /**
     * @param string $expectedNotification
     */
    private function assertNotification($expectedNotification)
    {
        $notifications = $this->orderDetails->getNotifications();
        $hasNotifications = '';

        foreach ($notifications as $notification) {
            $hasNotifications .= $notification;
            if ($notification === $expectedNotification) {
                return;
            }
        }

        throw new \RuntimeException(sprintf('There is no notification with "%s". Got "%s"', $expectedNotification, $hasNotifications));
    }
}
