<?php

namespace App\Actions\Orders;

use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Services\OrderReturnService;

class RejectReturnAction
{
    protected OrderReturnService $orderReturnService;

    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;
    }

    /**
     * Reject a return request
     *
     * @throws \Exception
     */
    public function execute(Order $order, ?string $rejectionReason = null): Order
    {
        if ($order->return_status !== ReturnStatus::RETURN_REQUESTED) {
            throw new \Exception('Return request is not in a valid state for rejection.');
        }

        return $this->orderReturnService->rejectReturn($order->id, $rejectionReason);
    }
}
