<?php

namespace App\Interfaces;

use App\Models\Order;

/**
 * Interface for payment URL generation
 * Follows Interface Segregation Principle - focused only on URL generation
 */
interface PaymentUrlProviderInterface
{
    /**
     * Get the success redirect URL for the payment
     */
    public function getSuccessRedirectUrl(Order $order): string;

    /**
     * Get the failure redirect URL for the payment
     */
    public function getFailureRedirectUrl(Order $order): string;

    /**
     * Get the webhook URL for the payment gateway
     */
    public function getWebhookUrl(): string;
}
