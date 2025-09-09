<?php

namespace App\Interfaces;

/**
 * Interface for payment validation
 * Follows Interface Segregation Principle - focused only on validation
 */
interface PaymentValidatorInterface
{
    /**
     * Validate payment response from the payment gateway
     *
     * @param  array  $params  The response parameters from the payment gateway
     * @return bool Whether the payment response is valid
     */
    public function validatePaymentResponse(array $params): bool;

    /**
     * Validate payment response from the payment gateway (webhook version)
     *
     * @param  string  $rawPayload  The raw JSON payload from the webhook
     * @param  array  $headers  The HTTP headers from the request
     * @return bool Whether the payment response is valid
     */
    public function validateWebhookPayload(string $rawPayload, array $headers): bool;
}
