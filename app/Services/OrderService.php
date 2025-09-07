<?php

namespace App\Services;

use App\DTOs\OrderPlacementData;
use App\DTOs\OrderEvaluationData;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PromotionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\ShippingCost;
use App\Models\Promotion;
use App\Services\InventoryManagementService;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;

class OrderService
{
    protected CartService $cartService;
    protected OrderEvaluationService $orderEvaluationService;
    protected InventoryManagementService $inventoryService;
    protected AdminNotificationService $adminNotificationService;

    /**
     * Create a new service instance.
     *
     * @param CartService $cartService
     * @param OrderEvaluationService $orderEvaluationService
     * @param InventoryManagementService $inventoryService
     * @param AdminNotificationService $adminNotificationService
     */
    public function __construct(
        CartService $cartService,
        OrderEvaluationService $orderEvaluationService,
        InventoryManagementService $inventoryService,
        AdminNotificationService $adminNotificationService
    ) {
        $this->cartService = $cartService;
        $this->orderEvaluationService = $orderEvaluationService;
        $this->inventoryService = $inventoryService;
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * Place a new order from the user's cart
     *
     * @param OrderPlacementData $orderData The data for placing an order
     *
     * @return Order The created order
     * @throws ModelNotFoundException If the address doesn't exist
     * @throws Exception If there was an error processing the order
     */
    public function placeOrderFromCart(OrderPlacementData $orderData): Order
    {
        $user = $this->getAuthenticatedUser();
        $this->validateCartNotEmpty();

        // Calculate order totals and get address using the OrderEvaluationService
        $orderEvaluation = $this->orderEvaluationService->evaluateOrderCalculation(
            $orderData->addressId,
            $orderData->couponCode,
            $orderData->promotionId
        );

        // Begin a transaction to ensure data consistency
        return DB::transaction(function () use (
            $user,
            $orderEvaluation,
            $orderData,
        ) {
            // Create the order
            $order = $this->createOrderRecord(
                $user,
                $orderEvaluation->address,
                $orderEvaluation,
                $orderData,
                $orderEvaluation->appliedPromotion
            );

            // Create the order items from cart items using CartService
            $this->cartService->toOrderItems($order);

            // Clear the cart after successful order creation
            $this->cartService->clearCart();

            // Send notification to admin about the new order
            $this->adminNotificationService->sendOrderPlacedNotification($order);

            // Return the created order with all its related items
            return $order->load('items.product', 'items.variant', 'shippingAddress');
        });
    }

    /**
     * Create the order record in the database
     *
     * @param mixed $user The authenticated user
     * @param Address $address The shipping address
     * @param OrderEvaluationData $orderEvaluation The order calculation results
     * @param OrderPlacementData $orderData The order placement data
     * @param Promotion|null $appliedPromotion The applied promotion
     * @return Order The created order
     */
    protected function createOrderRecord(
        $user,
        Address $address,
        OrderEvaluationData $orderEvaluation,
        OrderPlacementData $orderData,
        ?Promotion $appliedPromotion = null
    ): Order {
        $orderAttributes = [
            'user_id' => $user->id,
            'order_status' => OrderStatus::PROCESSING,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => $orderData->paymentMethod,
            'subtotal' => $orderEvaluation->subtotal,
            'shipping_cost' => $orderEvaluation->finalShippingCost,
            'discount' => $orderEvaluation->discount,
            'total' => $orderEvaluation->total,
            'coupon_code' => $orderData->couponCode,
            'promotion_id' => $appliedPromotion ? $appliedPromotion->id : null,
            'shipping_address_id' => $address->id,
            'notes' => $orderData->notes,
        ];

        return Order::create($orderAttributes);
    }

    /**
     * Get an order by its ID
     *
     * @param int $orderId
     * @return Order
     * @throws ModelNotFoundException If the order doesn't exist
     */
    public function getOrderById(int $orderId): Order
    {
        $user = $this->getAuthenticatedUser();

        return Order::where('user_id', $user->id)
            ->with(['items.product', 'items.variant', 'shippingAddress.area.gov'])
            ->findOrFail($orderId);
    }

    /**
     * Get all orders for the current user
     *
     * @param int|null $limit Optional limit for pagination
     * @return LengthAwarePaginator
     */
    public function getUserOrders(?int $limit = 10): LengthAwarePaginator
    {
        $user = $this->getAuthenticatedUser();

        return Order::where('user_id', $user->id)
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
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

    /**
     * Validate that the cart is not empty
     *
     * @return mixed The cart
     * @throws Exception If cart is empty
     */
    protected function validateCartNotEmpty()
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            throw new Exception('Cannot perform this action with an empty cart');
        }

        return $cart;
    }
}
