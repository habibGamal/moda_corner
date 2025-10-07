<?php

namespace App\Services;

use App\DTOs\CartSummaryData;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected CartItemResolverService $cartItemResolver;

    protected InventoryManagementService $inventoryService;

    public function __construct(
        InventoryManagementService $inventoryService,
        CartItemResolverService $cartItemResolver
    ) {
        $this->inventoryService = $inventoryService;
        $this->cartItemResolver = $cartItemResolver;
    }

    /**
     * Get the current user's cart or create one if it doesn't exist
     */
    public function getOrCreateCart(): Cart
    {
        $user = Auth::user()->load('cart');

        $cart = $user->cart;

        if (! $cart) {
            $cart = $user->cart()->create();
        }

        return $cart;
    }

    /**
     * Add a CartItem to the cart
     */
    public function addToCart(CartItem $item, int $quantity): CartItem
    {
        // Calculate new quantity (existing quantity + new quantity)
        $newQuantity = $item->quantity + $quantity;

        $this->inventoryService->validateCartItemStock($item, $newQuantity);

        $item->quantity = $newQuantity;

        $item->save();

        return $item;
    }

    /**
     * Update the quantity of a product in the cart
     */
    public function updateCartItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $this->inventoryService->validateCartItemStock($item, $quantity);

        $item->quantity = $quantity;

        $item->save();

        return $item;
    }

    /**
     * Remove an item from the cart
     *
     * @throws ModelNotFoundException
     */
    public function removeFromCart(CartItem $item): bool
    {
        // Check if the item still exists in the database
        if (! $item->exists) {
            throw new ModelNotFoundException("Cart item not found: {$item->id}");
        }

        $item->delete();

        return true;
    }

    /**
     * Clear all items from the cart
     */
    public function clearCart(): bool
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();

        return true;
    }

    /**
     * Get the current cart with its items and products
     */
    public function getCart(): Cart
    {
        $cart = $this->getOrCreateCart();

        // Eager load the items with their products and variants for better performance
        return $cart->load(['items.product', 'items.variant']);
    }

    /**
     * Get cart summary (total items, total price)
     */
    public function getCartSummary(): CartSummaryData
    {
        $cart = $this->getCart();
        $totalItems = $cart->items->sum('quantity');
        $totalPrice = $cart->getTotalPrice();

        return new CartSummaryData(
            totalItems: $totalItems,
            totalPrice: $totalPrice
        );
    }

    /**
     * Convert cart items to order items for a given order
     *
     * @param  Order  $order  The order to create items for
     */
    public function toOrderItems(Order $order): void
    {
        $cart = $this->getCart();

        foreach ($cart->items as $cartItem) {
            $this->cartItemResolver->toOrderItem($cartItem, $order);
        }
    }
}
