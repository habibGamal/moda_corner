<?php

namespace App\Interfaces;

use App\Models\Order;
use App\DTOs\PaymentResultData;

interface PaymentServiceInterface
{
    /**
     * Process payment for an order
     *
     * @param Order $order The order to process payment for
     * @return PaymentResultData The payment result data
     */
    public function pay(Order $order): PaymentResultData;

    /**
     * Validate payment response from the payment gateway
     *
     * @param array $params The response parameters from the payment gateway
     * @return bool Whether the payment response is valid
     */
    public function validatePaymentResponse(array $params): bool;

    /**
     * Process a successful payment for an order
     *
     * @param Order $order The order to update
     * @param array $paymentData The payment data from payment gateway
     * @return Order The updated order
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): Order;

    /**
     * Get the success redirect URL for the payment
     *
     * @param Order $order
     * @return string
     */
    public function getSuccessRedirectUrl(Order $order): string;

    /**
     * Get the failure redirect URL for the payment
     *
     * @param Order $order
     * @return string
     */
    public function getFailureRedirectUrl(Order $order): string;

    /**
     * Get the webhook URL for the payment gateway
     *
     * @return string
     */
    public function getWebhookUrl(): string;
}
