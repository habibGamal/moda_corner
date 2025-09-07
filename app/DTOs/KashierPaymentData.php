<?php

namespace App\DTOs;

class KashierPaymentData extends PaymentResultData
{
    /**
     * @param string $merchantId The Kashier merchant ID
     * @param string $orderId The order reference ID
     * @param string $amount The payment amount
     * @param string $currency The payment currency
     * @param string $hash The payment hash for validation
     * @param string $mode The payment mode (test/live)
     * @param string $redirectUrl The URL to redirect to after payment
     * @param string $failureUrl The URL to redirect to on payment failure
     * @param string $webhookUrl The URL for payment gateway webhooks
     * @param string $displayMode The language for display (ar/en)
     * @param string $paymentRequestId Unique request ID for the payment
     * @param string $allowedMethods Allowed payment methods (card, wallet, etc.)
     * @param array|null $additionalParams Additional parameters for the payment
     */
    public function __construct(
        string $merchantId,
        string $orderId,
        string $amount,
        string $currency,
        string $hash,
        string $mode,
        string $redirectUrl,
        string $failureUrl,
        string $webhookUrl,
        public readonly string $displayMode = 'ar',
        public readonly string $paymentRequestId = '',
        public readonly string $allowedMethods = 'card',
        ?array $additionalParams = null,
    ) {
        parent::__construct(
            $merchantId,
            $orderId,
            $amount,
            $currency,
            $hash,
            $mode,
            $redirectUrl,
            $failureUrl,
            $webhookUrl,
            $additionalParams,
        );
    }

    /**
     * Convert the DTO to an array for Kashier form submission
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'merchantId' => $this->merchantId,
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'hash' => $this->hash,
            'mode' => $this->mode,
            'merchantRedirect' => $this->redirectUrl,
            'failureRedirect' => $this->failureUrl,
            'serverWebhook' => $this->webhookUrl,
            'displayMode' => $this->displayMode,
            'paymentRequestId' => $this->paymentRequestId,
            'allowedMethods' => $this->allowedMethods,
        ];
    }
}
