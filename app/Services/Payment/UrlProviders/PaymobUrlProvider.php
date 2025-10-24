<?php

namespace App\Services\Payment\UrlProviders;

use App\Interfaces\PaymentUrlProviderInterface;
use App\Models\Order;

/**
 * Paymob-specific URL provider
 * Follows Single Responsibility Principle - only handles Paymob URLs
 */
class PaymobUrlProvider implements PaymentUrlProviderInterface
{
    /**
     * Get the redirect URL for successful payments
     */
    public function getSuccessRedirectUrl(Order $order): string
    {
        return route('payment.success', ['order' => $order->id]);
    }

    /**
     * Get the redirect URL for failed payments
     */
    public function getFailureRedirectUrl(Order $order): string
    {
        return route('payment.failure', ['order' => $order->id]);
    }

    /**
     * Get the webhook URL for server notifications from Paymob
     */
    public function getWebhookUrl(): string
    {
        return config('app.url') . '/webhooks/paymob';
    }
}
