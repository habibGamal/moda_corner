<?php

namespace App\Events\Payment;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when payment is initiated
 * Follows Single Responsibility Principle
 */
class PaymentInitiated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly array $paymentData = []
    ) {}
}
