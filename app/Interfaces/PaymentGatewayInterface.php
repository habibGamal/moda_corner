<?php

namespace App\Interfaces;

use App\DTOs\Payment\CardPaymentData;
use App\DTOs\Payment\PaymentResponseData;
use App\DTOs\Payment\PaymentValidationData;
use App\DTOs\Payment\WalletPaymentData;
use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     */
    public function getGatewayName(): string;

    /**
     * Process card payment without redirection
     *
     * @param  Order  $order  The order to process payment for
     * @param  CardPaymentData  $paymentData  The card payment data from the client
     * @return PaymentResponseData The payment processing result
     */
    public function payWithCard(Order $order, CardPaymentData $paymentData): PaymentResponseData;

    /**
     * Process wallet payment
     *
     * @param  Order  $order  The order to process payment for
     * @param  WalletPaymentData  $paymentData  The wallet payment data from the client
     * @return PaymentResponseData The payment processing result
     */
    public function payWithWallet(Order $order, WalletPaymentData $paymentData): PaymentResponseData;

    /**
     * Validate payment response from the payment gateway
     *
     * @param  array  $params  The response parameters from the payment gateway
     * @return PaymentValidationData The validation result
     */
    public function validatePaymentResponse(array $params): PaymentValidationData;

    /**
     * Process a successful payment for an order
     *
     * @param  Order  $order  The order to update
     * @param  array  $paymentData  The payment data from payment gateway
     * @return PaymentResponseData The payment processing result
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): PaymentResponseData;

    /**
     * Get the webhook URL for the payment gateway
     */
    public function getWebhookUrl(): string;

    /**
     * Check if the gateway supports the given payment method
     */
    public function supportsPaymentMethod(string $method): bool;

    /**
     * Get supported payment methods for this gateway
     */
    public function getSupportedPaymentMethods(): array;
}
