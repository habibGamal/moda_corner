<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentFailed;
use Illuminate\Support\Facades\Log;

/**
 * Listener for payment failure events
 * Follows Single Responsibility Principle
 */
class HandlePaymentFailed
{
    /**
     * Handle the event
     */
    public function handle(PaymentFailed $event): void
    {
        $order = $event->order;
        $paymentData = $event->paymentData;
        $reason = $event->reason;

        Log::warning('Payment failed', [
            'order_id' => $order->id,
            'payment_method' => $order->payment_method->value,
            'amount' => $order->total,
            'reason' => $reason,
            'payment_data' => $paymentData,
        ]);

        // You can add more actions here, such as:
        // - Sending failure notification emails
        // - Releasing reserved inventory
        // - Creating support tickets
        // - Sending SMS notifications
    }
}
