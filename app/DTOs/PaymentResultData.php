<?php

namespace App\DTOs;

class PaymentResultData
{
    /**
     * @param  string  $merchantId  The merchant ID
     * @param  string  $orderId  The order reference ID
     * @param  string  $amount  The payment amount
     * @param  string  $currency  The payment currency
     * @param  string  $hash  The payment hash for validation
     * @param  string  $mode  The payment mode (test/live)
     * @param  string  $redirectUrl  The URL to redirect to after payment
     * @param  string  $failureUrl  The URL to redirect to on payment failure
     * @param  string  $webhookUrl  The URL for payment gateway webhooks
     */
    public function __construct(
        public readonly string $merchantId,
        public readonly string $orderId,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $hash,
        public readonly string $mode,
        public readonly string $redirectUrl,
        public readonly string $failureUrl,
        public readonly string $webhookUrl,
        public readonly ?array $additionalParams = null,
    ) {}

    /**
     * Convert the DTO to an array
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
            'redirectUrl' => $this->redirectUrl,
            'failureUrl' => $this->failureUrl,
            'webhookUrl' => $this->webhookUrl,
            'additionalParams' => $this->additionalParams,
        ];
    }
}
