<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReturnOrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Notifications\ReturnOrderConfirmationNotification;
use App\Notifications\ReturnOrderStatusUpdatedNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnOrderService
{
    protected RefundService $refundService;

    protected InventoryManagementService $inventoryService;

    protected AdminNotificationService $adminNotificationService;

    /**
     * Create a new service instance.
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
     */
    public function isOrderEligibleForReturn(Order $order): bool
    {
        // Check if returns are enabled globally
        // if (! ReturnPolicyService::returnsEnabled()) {
        //     return false;
        // }

        // Check if order is delivered
        if ($order->order_status !== OrderStatus::DELIVERED) {
            return false;
        }

        // Check if within return window
        // if (! ReturnPolicyService::canReturn($order->delivered_at ?? $order->created_at)) {
        //     return false;
        // }

        // Check if already has pending/approved return
        if ($this->hasActiveReturn($order)) {
            return false;
        }

        return true;
    }

    /**
     * Check if an order has an active return
     */
    protected function hasActiveReturn(Order $order): bool
    {
        return $order->returnOrders()
            ->whereIn('status', [ReturnOrderStatus::REQUESTED, ReturnOrderStatus::APPROVED])
            ->exists();
    }

    /**
     * Check if order was delivered within return window
        if (!$order->delivered_at) {
            return false;
        }

        $deliveredDate = Carbon::parse($order->delivered_at);
        return $deliveredDate->diffInDays(now()) <= 14;
    }

    /**
     * Check if specific order items are eligible for return
     */
    public function areOrderItemsEligibleForReturn(Order $order, array $orderItemIds): bool
    {
        if (! $this->isOrderEligibleForReturn($order)) {
            return false;
        }

        // Check if any of the items have already been fully returned
        foreach ($orderItemIds as $orderItemId) {
            $orderItem = $order->items()->find($orderItemId);
            if (! $orderItem) {
                return false;
            }

            $returnedQuantity = ReturnOrderItem::where('order_item_id', $orderItemId)
                ->whereHas('returnOrder', function ($query) {
                    $query->whereIn('status', [ReturnOrderStatus::APPROVED, ReturnOrderStatus::COMPLETED]);
                })
                ->sum('quantity');

            if ($returnedQuantity >= $orderItem->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Request return for specific order items
     */
    public function requestReturn(int $orderId, array $returnItems, string $reason): ReturnOrder
    {
        $user = Auth::user();
        if (! $user) {
            throw new Exception('User must be authenticated to request return.');
        }

        $order = Order::where('user_id', $user->id)->findOrFail($orderId);

        $orderItemIds = array_column($returnItems, 'order_item_id');
        if (! $this->areOrderItemsEligibleForReturn($order, $orderItemIds)) {
            throw new Exception('Some items are not eligible for return.');
        }

        return DB::transaction(function () use ($order, $returnItems, $reason, $user) {
            // Create return order
            $returnOrder = ReturnOrder::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'return_number' => ReturnOrder::generateReturnNumber(),
                'status' => ReturnOrderStatus::REQUESTED,
                'reason' => $reason,
                'total_amount' => 0,
                'refund_amount' => 0,
                'requested_at' => now(),
            ]);

            $totalAmount = 0;

            // Create return items
            foreach ($returnItems as $item) {
                $orderItem = OrderItem::findOrFail($item['order_item_id']);

                // Validate quantity
                $maxReturnableQuantity = $this->getMaxReturnableQuantity($orderItem);
                if ($item['quantity'] > $maxReturnableQuantity) {
                    throw new Exception("Cannot return {$item['quantity']} items. Maximum returnable quantity is {$maxReturnableQuantity}.");
                }

                $subtotal = $item['quantity'] * $orderItem->unit_price;
                $totalAmount += $subtotal;

                ReturnOrderItem::create([
                    'return_order_id' => $returnOrder->id,
                    'order_item_id' => $orderItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $orderItem->unit_price,
                    'subtotal' => $subtotal,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            // Update return order total
            $returnOrder->update([
                'total_amount' => $totalAmount,
                'refund_amount' => $totalAmount, // Initial refund amount (may be adjusted later)
            ]);

            Log::info('Return requested', [
                'return_order_id' => $returnOrder->id,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'items_count' => count($returnItems),
                'total_amount' => $totalAmount,
            ]);

            // Send confirmation notification to customer
            $user->notify(new ReturnOrderConfirmationNotification($returnOrder));

            // Send notification to admin
            $this->adminNotificationService->sendReturnRequestNotification($returnOrder);

            return $returnOrder->fresh(['returnItems.orderItem.product', 'returnItems.orderItem.variant']);
        });
    }

    /**
     * Approve return request (Admin action)
     */
    public function approveReturn(int $returnOrderId): ReturnOrder
    {
        $returnOrder = ReturnOrder::findOrFail($returnOrderId);

        if ($returnOrder->status !== ReturnOrderStatus::REQUESTED) {
            throw new Exception('Return request is not in a valid state for approval.');
        }

        $previousStatus = $returnOrder->status;

        return DB::transaction(function () use ($returnOrder, $previousStatus) {
            $returnOrder->update([
                'status' => ReturnOrderStatus::APPROVED,
                'approved_at' => now(),
            ]);

            Log::info('Return approved', [
                'return_order_id' => $returnOrder->id,
                'order_id' => $returnOrder->order_id,
                'admin_id' => Auth::id(),
            ]);

            // Send status update notification to customer
            $returnOrder->order->user->notify(new ReturnOrderStatusUpdatedNotification($returnOrder, $previousStatus));

            return $returnOrder->fresh();
        });
    }

    /**
     * Reject return request (Admin action)
     */
    public function rejectReturn(int $returnOrderId, ?string $rejectionReason = null): ReturnOrder
    {
        $returnOrder = ReturnOrder::findOrFail($returnOrderId);

        if ($returnOrder->status !== ReturnOrderStatus::REQUESTED) {
            throw new Exception('Return request is not in a valid state for rejection.');
        }

        $previousStatus = $returnOrder->status;

        return DB::transaction(function () use ($returnOrder, $rejectionReason, $previousStatus) {
            $returnOrder->update([
                'status' => ReturnOrderStatus::REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            Log::info('Return rejected', [
                'return_order_id' => $returnOrder->id,
                'order_id' => $returnOrder->order_id,
                'admin_id' => Auth::id(),
                'rejection_reason' => $rejectionReason,
            ]);

            // Send status update notification to customer
            $returnOrder->order->user->notify(new ReturnOrderStatusUpdatedNotification($returnOrder, $previousStatus));

            return $returnOrder->fresh();
        });
    }

    /**
     * Complete return process (Admin action - when items are physically returned)
     */
    public function completeReturn(int $returnOrderId): ReturnOrder
    {
        $returnOrder = ReturnOrder::findOrFail($returnOrderId);

        if ($returnOrder->status !== ReturnOrderStatus::APPROVED) {
            throw new Exception('Return must be approved before it can be completed.');
        }

        $previousStatus = $returnOrder->status;

        return DB::transaction(function () use ($returnOrder, $previousStatus) {
            // Return items to stock
            foreach ($returnOrder->returnItems as $returnItem) {
                $orderItem = $returnItem->orderItem;
                if ($orderItem->variant_id && $orderItem->variant) {
                    $this->inventoryService->returnInventory(
                        $orderItem->variant,
                        $returnItem->quantity
                    );
                }
            }

            // Process refund if not Cash on Delivery
            $order = $returnOrder->order;
            if ($order->payment_method !== PaymentMethod::CASH_ON_DELIVERY) {
                // Use the new partial refund functionality
                $refundAmount = (float) $returnOrder->refund_amount;

                try {
                    $refundSuccess = $this->refundService->processPartialRefund($order, $refundAmount);

                    if ($refundSuccess) {
                        $returnOrder->update([
                            'refunded_at' => now(),
                        ]);

                        Log::info('Partial refund processed successfully', [
                            'return_order_id' => $returnOrder->id,
                            'order_id' => $order->id,
                            'refund_amount' => $refundAmount,
                            'original_amount' => $order->total,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Partial refund processing failed', [
                        'return_order_id' => $returnOrder->id,
                        'order_id' => $order->id,
                        'refund_amount' => $refundAmount,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                    // Don't fail the entire completion process for refund issues
                    // Admin can manually process the refund later
                }
            }

            $returnOrder->update([
                'status' => ReturnOrderStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            Log::info('Return completed', [
                'return_order_id' => $returnOrder->id,
                'order_id' => $returnOrder->order_id,
                'admin_id' => Auth::id(),
                'refund_amount' => $returnOrder->refund_amount,
                'refund_processed' => $order->payment_method !== PaymentMethod::CASH_ON_DELIVERY,
            ]);

            // Send completion notification to customer
            $returnOrder->order->user->notify(new ReturnOrderStatusUpdatedNotification($returnOrder, $previousStatus));

            return $returnOrder->fresh();
        });
    }

    /**
     * Get maximum returnable quantity for an order item
     */
    public function getMaxReturnableQuantity(OrderItem $orderItem): int
    {
        $alreadyReturnedQuantity = ReturnOrderItem::where('order_item_id', $orderItem->id)
            ->whereHas('returnOrder', function ($query) {
                $query->whereIn('status', [ReturnOrderStatus::APPROVED, ReturnOrderStatus::COMPLETED]);
            })
            ->sum('quantity');

        return $orderItem->quantity - $alreadyReturnedQuantity;
    }

    /**
     * Get all pending return orders
     */
    public function getPendingReturnOrders()
    {
        return ReturnOrder::where('status', ReturnOrderStatus::REQUESTED)
            ->with([
                'order.user',
                'order.shippingAddress',
                'returnItems.orderItem.product',
                'returnItems.orderItem.variant',
            ])
            ->orderBy('requested_at', 'asc')
            ->get();
    }

    /**
     * Get return statistics
     */
    public function getReturnStatistics(): array
    {
        return [
            'pending_requests' => ReturnOrder::where('status', ReturnOrderStatus::REQUESTED)->count(),
            'approved_returns' => ReturnOrder::where('status', ReturnOrderStatus::APPROVED)->count(),
            'completed_returns' => ReturnOrder::where('status', ReturnOrderStatus::COMPLETED)->count(),
            'rejected_returns' => ReturnOrder::where('status', ReturnOrderStatus::REJECTED)->count(),
            'total_refund_amount' => ReturnOrder::where('status', ReturnOrderStatus::COMPLETED)->sum('refund_amount'),
        ];
    }

    /**
     * Get user's return history
     */
    public function getUserReturnHistory(?int $userId = null)
    {
        $userId = $userId ?? Auth::id();

        return ReturnOrder::where('user_id', $userId)
            ->with([
                'order',
                'returnItems.orderItem.product',
                'returnItems.orderItem.variant',
            ])
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    /**
     * Get return order by return number
     */
    public function getByReturnNumber(string $returnNumber): ?ReturnOrder
    {
        return ReturnOrder::where('return_number', $returnNumber)
            ->with([
                'order.user',
                'order.shippingAddress',
                'returnItems.orderItem.product',
                'returnItems.orderItem.variant',
            ])
            ->first();
    }

    /**
     * Get returns for a specific order
     */
    public function getOrderReturns(int $orderId)
    {
        return ReturnOrder::where('order_id', $orderId)
            ->with([
                'returnItems.orderItem.product',
                'returnItems.orderItem.variant',
            ])
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    /**
     * Calculate total returned amount for an order
     */
    public function getOrderTotalReturnedAmount(int $orderId): float
    {
        return ReturnOrder::where('order_id', $orderId)
            ->whereIn('status', [ReturnOrderStatus::APPROVED, ReturnOrderStatus::COMPLETED])
            ->sum('refund_amount');
    }

    /**
     * Check if an order has any active return requests
     */
    public function hasActiveReturnRequests(int $orderId): bool
    {
        return ReturnOrder::where('order_id', $orderId)
            ->whereIn('status', [ReturnOrderStatus::REQUESTED, ReturnOrderStatus::APPROVED])
            ->exists();
    }

    /**
     * Update refund amount for a return order (Admin action)
     */
    public function updateRefundAmount(int $returnOrderId, float $refundAmount): ReturnOrder
    {
        $returnOrder = ReturnOrder::findOrFail($returnOrderId);

        if ($returnOrder->status === ReturnOrderStatus::COMPLETED) {
            throw new Exception('Cannot update refund amount for completed returns.');
        }

        // Validate refund amount
        if ($refundAmount < 0) {
            throw new Exception('Refund amount cannot be negative.');
        }

        $maxRefundable = $this->refundService->getMaxRefundableAmount($returnOrder->order);
        if ($refundAmount > $maxRefundable) {
            throw new Exception("Refund amount cannot exceed maximum refundable amount of {$maxRefundable}.");
        }

        $returnOrder->update([
            'refund_amount' => $refundAmount,
        ]);

        Log::info('Return refund amount updated', [
            'return_order_id' => $returnOrder->id,
            'order_id' => $returnOrder->order_id,
            'old_amount' => $returnOrder->getOriginal('refund_amount'),
            'new_amount' => $refundAmount,
            'admin_id' => Auth::id(),
        ]);

        return $returnOrder->fresh();
    }

    /**
     * Calculate estimated refund amount for return items
     */
    public function calculateEstimatedRefund(array $returnItems): float
    {
        return $this->refundService->calculateRefundAmountForItems($returnItems);
    }
}
