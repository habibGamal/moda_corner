<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Services\OrderCancellationService;

class CancelOrderAction
{
    protected OrderCancellationService $orderCancellationService;

    public function __construct(OrderCancellationService $orderCancellationService)
    {
        $this->orderCancellationService = $orderCancellationService;
    }

    /**
     * Cancel an order (admin action)
     *
     * @throws \Exception
     */
    public function execute(Order $order, ?string $reason = null): Order
    {
        $this->orderCancellationService->cancelOrder($order->id);

        return $order->fresh();
    }
}
