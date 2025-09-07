<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancellationNotification;
use App\Services\InventoryManagementService;
use App\Services\RefundService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class OrderCancellationService
{
    protected InventoryManagementService $inventoryService;
    protected RefundService $refundService;

    /**
     * Create a new service instance.
     *
     * @param InventoryManagementService $inventoryService
     * @param RefundService $refundService
     */
    public function __construct(InventoryManagementService $inventoryService, RefundService $refundService)
    {
        $this->inventoryService = $inventoryService;
        $this->refundService = $refundService;
    }    /**
         * Cancel an order and handle all related operations
         *
         * @param int $orderId
         * @param string|null $reason
         * @return Order
         * @throws ModelNotFoundException If the order doesn't exist
         * @throws Exception If the order cannot be cancelled
         */
    public function cancelOrder(int $orderId, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($orderId, $reason) {
            $order = Order::findOrFail($orderId);

            // Check if the order can be cancelled
            if (!$order->canBeCancelled()) {
                throw new Exception('Only orders in processing status can be cancelled');
            }

            // Update order status
            $order->order_status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            if ($reason) {
                $order->cancellation_reason = $reason;
            }
            $order->save();

            // Return inventory to stock using the inventory service
            $this->inventoryService->returnOrderInventoryToStock($order);
            // Send notifications
            $this->sendCancellationNotifications($order);

            Log::info('Order cancelled successfully', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method->value,
                'payment_status' => $order->payment_status->value,
                'reason' => $reason,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Check if an order needs refund processing
     *
     * @param Order $order
     * @return bool
     */
    public function needsRefund(Order $order): bool
    {
        return $order->needs_refund;
    }

    /**
     * Process refund for a cancelled order (admin action)
     *
     * @param Order $order
     * @return Order
     * @throws Exception If refund cannot be processed
     */
    public function processRefund(Order $order): Order
    {
        if (!$this->needsRefund($order)) {
            throw new Exception('This order does not require a refund');
        }

        return DB::transaction(function () use ($order) {
            // Process the actual refund through the RefundService
            $refundSuccess = $this->refundService->processRefund($order);

            if (!$refundSuccess) {
                throw new Exception('Refund processing failed');
            }

            $order->payment_status = PaymentStatus::REFUNDED;
            $order->refunded_at = now();
            $order->save();

            Log::info('Order refund processed', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => $order->total,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Send cancellation notifications to user and admin
     *
     * @param Order $order
     * @return void
     */
    protected function sendCancellationNotifications(Order $order): void
    {
        try {
            // Send notification to the customer
            $order->user->notify(new OrderCancellationNotification($order, 'customer'));

            // Send notification to admin users
            $adminUsers = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            if ($adminUsers->isNotEmpty()) {
                Notification::send($adminUsers, new OrderCancellationNotification($order, 'admin'));
            }

            Log::info('Cancellation notifications sent', [
                'order_id' => $order->id,
                'admin_count' => $adminUsers->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send cancellation notifications', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception as the order cancellation itself was successful
        }
    }

    /**
     * Get authenticated user or throw exception
     *
     * @return mixed The authenticated user
     * @throws Exception If user is not authenticated
     */
    protected function getAuthenticatedUser()
    {
        $user = Auth::user();

        if (!$user) {
            throw new Exception('User must be authenticated to perform this action');
        }

        return $user;
    }
}
