<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RefundService;
use App\Services\InventoryManagementService;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Exception;

class OrderReturnService
{
    protected RefundService $refundService;
    protected InventoryManagementService $inventoryService;
    protected AdminNotificationService $adminNotificationService;

    /**
     * Create a new service instance.
     *
     * @param RefundService $refundService
     * @param InventoryManagementService $inventoryService
     * @param AdminNotificationService $adminNotificationService
     */
    public function __construct(
        RefundService $refundService,
        InventoryManagementService $inventoryService,
        AdminNotificationService $adminNotificationService
    ) {
        $this->refundService = $refundService;
        $this->inventoryService = $inventoryService;
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * Check if an order is eligible for return
     *
     * @param Order $order
     * @return bool
     */
    public function isOrderEligibleForReturn(Order $order): bool
    {
        // Check if order is SHIPPED
        if ($order->order_status !== OrderStatus::DELIVERED) {
            return false;
        }
        // Check if order has not already been returned
        if (!is_null($order->return_status)) {
            return false;
        }

        // Check if order was delivered within 14 days
        if (!$order->delivered_at) {
            return false;
        }

        $deliveredDate = Carbon::parse($order->delivered_at);
        return $deliveredDate->diffInDays(now()) <= 14;
    }

    /**
     * Request return for an order
     *
     * @param int $orderId
     * @param string $reason
     * @return Order
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function requestReturn(int $orderId, string $reason): Order
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('User must be authenticated to request return.');
        }

        $order = Order::where('user_id', $user->id)->findOrFail($orderId);

        if (!$this->isOrderEligibleForReturn($order)) {
            throw new Exception('This order is not eligible for return.');
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'return_status' => ReturnStatus::RETURN_REQUESTED,
                'return_requested_at' => now(),
                'return_reason' => $reason,
            ]);

            Log::info('Return requested for order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'reason' => $reason,
            ]);

            // Send notification to admin about the return request
            $this->adminNotificationService->sendOrderReturnRequestNotification($order);

            return $order->fresh();
        });
    }

    /**
     * Approve return request (Admin action)
     *
     * @param int $orderId
     * @return Order
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function approveReturn(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);

        if ($order->return_status !== ReturnStatus::RETURN_REQUESTED) {
            throw new Exception('Return request is not in a valid state for approval.');
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'return_status' => ReturnStatus::RETURN_APPROVED,
            ]);

            Log::info('Return approved for order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'admin_id' => Auth::id(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Reject return request (Admin action)
     *
     * @param int $orderId
     * @param string|null $rejectionReason
     * @return Order
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function rejectReturn(int $orderId, ?string $rejectionReason = null): Order
    {
        $order = Order::findOrFail($orderId);

        if ($order->return_status !== ReturnStatus::RETURN_REQUESTED) {
            throw new Exception('Return request is not in a valid state for rejection.');
        }

        return DB::transaction(function () use ($order, $rejectionReason) {
            $updateData = ['return_status' => ReturnStatus::RETURN_REJECTED];

            if ($rejectionReason) {
                $updateData['return_reason'] = $order->return_reason . ' | Rejection: ' . $rejectionReason;
            }

            $order->update($updateData);

            Log::info('Return rejected for order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'admin_id' => Auth::id(),
                'rejection_reason' => $rejectionReason,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Complete return process (Admin action - when item is physically returned)
     *
     * @param int $orderId
     * @return Order
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function completeReturn(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);

        if ($order->return_status !== ReturnStatus::RETURN_APPROVED) {
            throw new Exception('Return must be approved before it can be completed.');
        }

        return DB::transaction(function () use ($order) {
            // Return items to stock using the inventory service
            $this->inventoryService->returnOrderInventoryToStock($order);

            $updateData = [
                'payment_status' => PaymentStatus::REFUNDED,
                'refunded_at' => now()
            ];

            // Process refund if not Cash on Delivery
            if ($order->payment_method !== PaymentMethod::CASH_ON_DELIVERY) {
                $this->refundService->processRefund($order);
                $updateData['return_status'] = ReturnStatus::REFUND_PROCESSED;
            } else {
                $updateData['return_status'] = ReturnStatus::ITEM_RETURNED;
            }

            // Update return status and payment status
            $order->update($updateData);

            Log::info('Return completed for order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'admin_id' => Auth::id(),
                'refund_processed' => $order->payment_method !== PaymentMethod::CASH_ON_DELIVERY,
            ]);

            return $order->fresh();
        });
    }    /**
         * Get all orders that are pending return approval
         *
         * @return \Illuminate\Database\Eloquent\Collection
         */
    public function getPendingReturnOrders()
    {
        return Order::where('return_status', ReturnStatus::RETURN_REQUESTED)
            ->with(['user', 'items.product', 'items.variant', 'shippingAddress'])
            ->orderBy('return_requested_at', 'asc')
            ->get();
    }

    /**
     * Get return statistics
     *
     * @return array
     */
    public function getReturnStatistics(): array
    {
        return [
            'pending_requests' => Order::where('return_status', ReturnStatus::RETURN_REQUESTED)->count(),
            'approved_returns' => Order::where('return_status', ReturnStatus::RETURN_APPROVED)->count(),
            'completed_returns' => Order::whereIn('return_status', [
                ReturnStatus::ITEM_RETURNED,
                ReturnStatus::REFUND_PROCESSED
            ])->count(),
            'rejected_returns' => Order::where('return_status', ReturnStatus::RETURN_REJECTED)->count(),
        ];
    }

    /**
     * Get user's return history
     *
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserReturnHistory(?int $userId = null)
    {
        $userId = $userId ?? Auth::id();

        return Order::where('user_id', $userId)
            ->whereNotNull('return_status')
            ->with(['items.product', 'items.variant'])
            ->orderBy('return_requested_at', 'desc')
            ->get();
    }
}
