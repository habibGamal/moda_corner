<?php

namespace App\DTOs;

class PaymentResultData
{
    /**
     * @param string $merchantId The merchant ID
     * @param string $orderId The order reference ID
     * @param string $amount The payment amount
     * @param string $currency The payment currency
     * @param string $hash The payment hash for validation
     * @param string $mode The payment mode (test/live)
     * @param string $redirectUrl The URL to redirect to after payment
     * @param string $failureUrl The URL to redirect to on payment failure
     * @param string $webhookUrl The URL for payment gateway webhooks
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
    ) {
    }
}
