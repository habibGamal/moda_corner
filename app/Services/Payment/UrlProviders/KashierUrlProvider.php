<?php

namespace App\Services\Payment\UrlProviders;

use App\Interfaces\PaymentUrlProviderInterface;
use App\Models\Order;

/**
 * Kashier-specific URL provider
 * Follows Single Responsibility Principle - only handles Kashier URLs
 */
class KashierUrlProvider implements PaymentUrlProviderInterface
{
    /**
     * Get the redirect URL for successful payments
     */
    public function getSuccessRedirectUrl(Order $order): string
    {
        return route('kashier.payment.success', ['order' => $order->id]);
    }

    /**
     * Get the redirect URL for failed payments
     */
    public function getFailureRedirectUrl(Order $order): string
    {
        return route('kashier.payment.failure', ['order' => $order->id]);
    }

    /**
     * Get the webhook URL for server notifications from Kashier
     */
    public function getWebhookUrl(): string
    {
        return config('app.url').'/webhooks/kashier';
    }
}
