<?php

namespace App\DTOs;

/**
 * Paymob-specific payment data DTO
 * Extends PaymentResultData with Paymob-specific fields
 */
class PaymobPaymentData extends PaymentResultData
{
    /**
     * @param  string  $merchantId  The Paymob public key (used as merchant identifier)
     * @param  string  $orderId  The order reference ID
     * @param  string  $amount  The payment amount
     * @param  string  $currency  The payment currency
     * @param  string  $hash  Not used in Paymob Unified Checkout (kept for compatibility)
     * @param  string  $mode  The payment mode (test/live)
     * @param  string  $redirectUrl  The URL to redirect to after payment
     * @param  string  $failureUrl  The URL to redirect to on payment failure
     * @param  string  $webhookUrl  The URL for payment gateway webhooks
     * @param  string  $iframeUrl  The Paymob iframe/checkout URL
     * @param  string  $intentionId  The Paymob intention ID
     * @param  string  $clientSecret  The client secret for the payment session
     * @param  array|null  $additionalParams  Additional parameters for the payment
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
        public readonly string $iframeUrl = '',
        public readonly string $intentionId = '',
        public readonly string $clientSecret = '',
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
     * Convert the DTO to an array for Paymob frontend usage
     */
    public function toArray(): array
    {
        return [
            'merchantId' => $this->merchantId,
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'mode' => $this->mode,
            'redirectUrl' => $this->redirectUrl,
            'failureUrl' => $this->failureUrl,
            'webhookUrl' => $this->webhookUrl,
            'iframeUrl' => $this->iframeUrl,
            'intentionId' => $this->intentionId,
            'clientSecret' => $this->clientSecret,
            'additionalParams' => $this->additionalParams,
        ];
    }
}
