<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductVariantNotFoundException;
use App\Exceptions\InsufficientStockException;
use App\Services\InventoryManagementService;

class CartItemResolverService
{
    protected InventoryManagementService $inventoryService;

    public function __construct(InventoryManagementService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    /**
     * Resolve and prepare a CartItem for adding to cart.
     * Verifies product & variant existence, checks stock availability,
     * and determines if the item already exists in the cart.
     *
     * @param Cart $cart
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return CartItem
     * @throws ProductNotFoundException
     * @throws ProductVariantNotFoundException
     * @throws InsufficientStockException
     */
    public function resolveCartItem(Cart $cart, int $productId, ?int $variantId = null): CartItem
    {
        // Verify product exists and is active
        $product = $this->verifyProduct($productId);

        // Resolve the appropriate variant
        $variant = $this->resolveVariant($product, $variantId);

        // Check if item already exists in cart
        $existingCartItem = $this->findExistingCartItem($cart, $productId, $variant->id);

        if ($existingCartItem) {
            // Return existing cart item without modifying quantity
            return $existingCartItem;
        }

        // Create new cart item (not saved yet) with quantity 0
        $cartItem = new CartItem([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        return $cartItem;
    }

    /**
     * Verify that the product exists and is active.
     *
     * @param int $productId
     * @return Product
     * @throws ProductNotFoundException
     */
    protected function verifyProduct(int $productId): Product
    {
        $product = Product::where('id', $productId)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            throw new ProductNotFoundException($productId);
        }

        return $product;
    }

    /**
     * Resolve the appropriate variant for the product.
     *
     * @param Product $product
     * @param int|null $variantId
     * @return ProductVariant
     * @throws ProductVariantNotFoundException
     */
    protected function resolveVariant(Product $product, ?int $variantId = null): ProductVariant
    {
        if ($variantId) {
            // Specific variant requested
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if (!$variant) {
                throw new ProductVariantNotFoundException($variantId);
            }

            return $variant;
        }

        // Use default variant if no specific variant requested
        $variant = $product->variants()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if (!$variant) {
            // Fallback to first active variant if no default is set
            $variant = $product->variants()
                ->where('is_active', true)
                ->first();
        }

        if (!$variant) {
            throw new ProductVariantNotFoundException(0);
        }

        return $variant;
    }

    /**
     * Find existing cart item with the same product and variant.
     *
     * @param Cart $cart
     * @param int $productId
     * @param int $variantId
     * @return CartItem|null
     */
    protected function findExistingCartItem(Cart $cart, int $productId, int $variantId): ?CartItem
    {
        return $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();
    }

    /**
     * Convert a CartItem to an OrderItem for a given order
     * Requires that the cart item has a valid variant assigned
     *
     * @param CartItem $cartItem The cart item to convert
     * @param Order $order The order to associate the item with
     * @return OrderItem The created order item
     * @throws \Exception If the cart item has no variant
     */
    public function toOrderItem(CartItem $cartItem, Order $order): OrderItem
    {
        $product = $cartItem->product;
        $variant = $cartItem->variant;

        // Ensure variant is always defined
        if (!$variant) {
            throw new \Exception('Cart item must have a variant to be converted to order item. Product: ' . $product->name_en);
        }

        // Ensure we have the actual variant model, not just the relationship
        if (!$variant instanceof ProductVariant) {
            $variant = ProductVariant::find($cartItem->product_variant_id);
            if (!$variant) {
                throw new \Exception('Product variant not found for cart item. Variant ID: ' . $cartItem->product_variant_id);
            }
        }

        $unitPrice = $cartItem->getUnitPrice();

        // Create the order item
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => $cartItem->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $cartItem->quantity,
        ]);

        // Reserve inventory using the inventory service
        $this->inventoryService->reserveInventory($variant, $cartItem->quantity);

        return $orderItem;
    }
}
