<?php

namespace App\Actions\Orders;

use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Services\OrderReturnService;

class CompleteReturnAction
{
    protected OrderReturnService $orderReturnService;

    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;
    }

    /**
     * Complete a return request
     *
     * @param Order $order
     * @return Order
     * @throws \Exception
     */
    public function execute(Order $order): Order
    {
        if ($order->return_status !== ReturnStatus::RETURN_APPROVED) {
            throw new \Exception('Return must be approved before it can be completed.');
        }

        return $this->orderReturnService->completeReturn($order->id);
    }
}
