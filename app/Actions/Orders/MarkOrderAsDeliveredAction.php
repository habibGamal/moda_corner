<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;

class MarkOrderAsDeliveredAction
{
    /**
     * Mark an order as delivered
     *
     * @param Order $order
     * @return Order
     * @throws \Exception
     */
    public function execute(Order $order): Order
    {
        if (!in_array($order->order_status, [OrderStatus::PROCESSING, OrderStatus::SHIPPED])) {
            throw new \Exception('Only processing or shipped orders can be marked as delivered.');
        }

        $order->update([
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);

        return $order->fresh();
    }
}
