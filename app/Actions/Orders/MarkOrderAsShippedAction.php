<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;

class MarkOrderAsShippedAction
{
    /**
     * Mark an order as shipped
     *
     * @param Order $order
     * @return Order
     * @throws \Exception
     */
    public function execute(Order $order): Order
    {
        if ($order->order_status !== OrderStatus::PROCESSING) {
            throw new \Exception('Only processing orders can be marked as shipped.');
        }

        $order->update([
            'order_status' => OrderStatus::SHIPPED,
        ]);

        return $order->fresh();
    }
}
