<?php

namespace App\Actions\Orders;

use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Services\OrderReturnService;

class ApproveReturnAction
{
    protected OrderReturnService $orderReturnService;

    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;
    }

    /**
     * Approve a return request
     *
     * @throws \Exception
     */
    public function execute(Order $order): Order
    {
        if ($order->return_status !== ReturnStatus::RETURN_REQUESTED) {
            throw new \Exception('Return request is not in a valid state for approval.');
        }

        return $this->orderReturnService->approveReturn($order->id);
    }
}
