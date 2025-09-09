<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentSucceeded;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Listener for payment success events
 * Follows Single Responsibility Principle
 */
class HandlePaymentSucceeded
{
    public function __construct(
        private AdminNotificationService $adminNotificationService
    ) {}

    /**
     * Handle the event
     */
    public function handle(PaymentSucceeded $event): void
    {
        $order = $event->order;
        $paymentData = $event->paymentData;

        Log::info('Payment successful', [
            'order_id' => $order->id,
            'payment_method' => $order->payment_method->value,
            'amount' => $order->total,
            'payment_id' => $order->payment_id,
        ]);

        // You can add more listeners or actions here, such as:
        // - Sending confirmation emails
        // - Updating inventory
        // - Triggering fulfillment processes
        // - Sending SMS notifications
    }
}
