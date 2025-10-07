<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * InventoryManagementService
 *
 * Centralized service for handling all inventory-related operations including
 * stock validation, reservation, and return operations with thread-safe transactions.
 *
 * Key Features:
 * - Cart item stock validation with detailed product information
 * - Inventory reservation during order creation with database locking
 * - Inventory return for order cancellations and returns
 * - Thread-safe operations using DB transactions and row-level locking
 * - Comprehensive logging for inventory changes and audit trails
 * - Exception handling with detailed stock information for better UX
 */
class InventoryManagementService
{
    /**
     * Validate stock for a cart item and throw exception with detailed product information.
     * This method is specifically for cart items that already have product relationships loaded.
     *
     * @throws InsufficientStockException
     */
    public function validateCartItemStock(CartItem $item, int $requestedQuantity): void
    {
        $variant = $item->variant;
        $product = $item->product;

        if ($variant && $requestedQuantity > $variant->quantity) {
            // $productName = $product->name_en ?? $product->name_ar ?? "Product #{$product->id}";
            $productName = (! empty($product->name_en)) ? $product->name_en :
                         ((! empty($product->name_ar)) ? $product->name_ar : "Product #{$product->id}");

            throw new InsufficientStockException($requestedQuantity, $variant->quantity, $productName);
        }
    }

    /**
     * Reserve inventory for an order item (decrease stock).
     * This is typically called when converting cart items to order items.
     *
     * @throws InsufficientStockException
     */
    public function reserveInventory(ProductVariant $variant, int $quantity): bool
    {
        return DB::transaction(function () use ($variant, $quantity) {
            // Lock the variant for update to prevent race conditions
            $lockedVariant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();

            if (! $lockedVariant) {
                throw new \Exception('Product variant not found during inventory reservation.');
            }

            // Check if there's sufficient stock
            if ($lockedVariant->quantity < $quantity) {
                throw new InsufficientStockException(
                    $quantity,
                    $lockedVariant->quantity,
                    $lockedVariant->product->name_en ?? $lockedVariant->product->name_ar ?? "Product #{$lockedVariant->product_id}"
                );
            }

            // Reserve the inventory
            $lockedVariant->decrement('quantity', $quantity);

            Log::info('Inventory reserved', [
                'variant_id' => $lockedVariant->id,
                'product_id' => $lockedVariant->product_id,
                'quantity_reserved' => $quantity,
                'remaining_stock' => $lockedVariant->fresh()->quantity,
            ]);

            return true;
        });
    }

    /**
     * Return inventory to stock (increase stock).
     * This is typically called when cancelling orders or processing returns.
     */
    public function returnInventory(ProductVariant $variant, int $quantity): bool
    {
        return DB::transaction(function () use ($variant, $quantity) {
            // Lock the variant for update to prevent race conditions
            $lockedVariant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();

            if (! $lockedVariant) {
                throw new \Exception('Product variant not found during inventory return.');
            }

            // Return the inventory
            $lockedVariant->increment('quantity', $quantity);

            Log::info('Inventory returned', [
                'variant_id' => $lockedVariant->id,
                'product_id' => $lockedVariant->product_id,
                'quantity_returned' => $quantity,
                'new_stock' => $lockedVariant->fresh()->quantity,
            ]);

            return true;
        });
    }

    /**
     * Return inventory to stock for all items in an order.
     * This is typically called when cancelling orders.
     */
    public function returnOrderInventoryToStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variant_id && $item->variant) {
                $this->returnInventory($item->variant, $item->quantity);
            }
        }

        Log::info('Order inventory returned to stock', [
            'order_id' => $order->id,
            'items_count' => $order->items->count(),
        ]);
    }
}
