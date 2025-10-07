<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderCancellationService;

class ProcessRefundAction
{
    protected OrderCancellationService $orderCancellationService;

    public function __construct(OrderCancellationService $orderCancellationService)
    {
        $this->orderCancellationService = $orderCancellationService;
    }

    /**
     * Process refund for a cancelled order (admin action)
     *
     * @throws \Exception
     */
    public function execute(Order $order): Order
    {
        // Validate that the order can be refunded
        if ($order->order_status !== OrderStatus::CANCELLED) {
            throw new \Exception('Only cancelled orders can be refunded.');
        }

        if ($order->payment_status === PaymentStatus::REFUNDED) {
            throw new \Exception('This order has already been refunded.');
        }

        if ($order->payment_status !== PaymentStatus::PAID) {
            throw new \Exception('Only paid orders can be refunded.');
        }

        if ($order->payment_method->isCOD()) {
            throw new \Exception('Cash on delivery orders do not require refunds.');
        }

        // Process the refund using the cancellation service
        return $this->orderCancellationService->processRefund($order);
    }
}
