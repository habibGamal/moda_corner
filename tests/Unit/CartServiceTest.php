<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cartService = app(CartService::class);
    Auth::login($this->user);
});

describe('getOrCreateCart', function () {
    it('creates a new cart if user does not have one', function () {
        // Ensure user has no cart
        expect($this->user->cart)->toBeNull();

        $cart = $this->cartService->getOrCreateCart();

        expect($cart)->toBeInstanceOf(Cart::class);
        expect($cart->user_id)->toBe($this->user->id);
        expect($cart->exists)->toBeTrue();
    });

    it('returns existing cart if user already has one', function () {
        // Create an existing cart
        $existingCart = Cart::factory()->create(['user_id' => $this->user->id]);

        $cart = $this->cartService->getOrCreateCart();

        expect($cart->id)->toBe($existingCart->id);
        expect(Cart::where('user_id', $this->user->id)->count())->toBe(1);
    });
});

describe('addToCart', function () {
    it('adds quantity to existing cart item', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $result = $this->cartService->addToCart($cartItem, 3);

        expect($result->quantity)->toBe(5);
        expect($cartItem->fresh()->quantity)->toBe(5);
    });

    it('throws exception when insufficient stock', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        expect(function () use ($cartItem) {
            $this->cartService->addToCart($cartItem, 5); // 2 + 5 = 7, but only 3 available
        })->toThrow(InsufficientStockException::class);
    });
});

describe('updateCartItemQuantity', function () {
    it('updates cart item quantity successfully', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $result = $this->cartService->updateCartItemQuantity($cartItem, 5);

        expect($result->quantity)->toBe(5);
        expect($cartItem->fresh()->quantity)->toBe(5);
    });

    it('throws exception when updating to quantity exceeding stock', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        expect(function () use ($cartItem) {
            $this->cartService->updateCartItemQuantity($cartItem, 5);
        })->toThrow(InsufficientStockException::class);
    });
});

describe('removeFromCart', function () {
    it('removes cart item successfully', function () {
        $product = Product::factory()->create();
        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $result = $this->cartService->removeFromCart($cartItem);

        expect($result)->toBeTrue();
        expect(CartItem::find($cartItem->id))->toBeNull();
    });

    it('handles deletion of already deleted cart item gracefully', function () {
        $product = Product::factory()->create();
        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Delete the item first
        $cartItem->delete();

        // Should throw exception when trying to delete non-existent item
        expect(function () use ($cartItem) {
            $this->cartService->removeFromCart($cartItem);
        })->toThrow(ModelNotFoundException::class);
    });
});

describe('clearCart', function () {
    it('removes all items from cart', function () {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $cart = $this->cartService->getOrCreateCart();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        expect($cart->items()->count())->toBe(2);

        $this->cartService->clearCart();

        expect($cart->items()->count())->toBe(0);
    });
});

describe('getCart', function () {
    it('returns cart with eager loaded relationships', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $result = $this->cartService->getCart();

        expect($result)->toBeInstanceOf(Cart::class);
        expect($result->items)->toHaveCount(1);
        expect($result->items->first()->product)->toBeInstanceOf(Product::class);
        expect($result->items->first()->variant)->toBeInstanceOf(ProductVariant::class);

        // Check that relationships are loaded (not lazy loaded)
        expect($result->relationLoaded('items'))->toBeTrue();
        expect($result->items->first()->relationLoaded('product'))->toBeTrue();
        expect($result->items->first()->relationLoaded('variant'))->toBeTrue();
    });

    it('returns empty cart when no items exist', function () {
        $cart = $this->cartService->getCart();

        expect($cart)->toBeInstanceOf(Cart::class);
        expect($cart->items)->toHaveCount(0);
    });
});

describe('getCartSummary', function () {
    it('calculates correct summary for cart with items', function () {
        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 200]);

        $cart = $this->cartService->getOrCreateCart();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $summary = $this->cartService->getCartSummary();

        expect($summary->totalItems)->toBe(5); // 2 + 3
        expect($summary->totalPrice)->toBe(800.0); // (2 * 100) + (3 * 200)
    });

    it('returns zero summary for empty cart', function () {
        $summary = $this->cartService->getCartSummary();

        expect($summary->totalItems)->toBe(0);
        expect($summary->totalPrice)->toBe(0.0);
    });

    it('returns correct summary structure', function () {
        $summary = $this->cartService->getCartSummary();

        expect($summary)->toBeInstanceOf(\App\DTOs\CartSummaryData::class);
        expect($summary->totalItems)->toBeInt();
        expect($summary->totalPrice)->toBeFloat();
    });
});

describe('Cart Service Integration', function () {
    it('handles complete cart workflow', function () {
        // Create products
        $product1 = Product::factory()->create(['price' => 150]);
        $product2 = Product::factory()->create(['price' => 250]);

        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10,
        ]);
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 5,
        ]);

        // Get cart
        $cart = $this->cartService->getOrCreateCart();
        expect($cart->items)->toHaveCount(0);

        // Add items
        $item1 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'product_variant_id' => $variant1->id,
            'quantity' => 1,
        ]);

        $item2 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'product_variant_id' => $variant2->id,
            'quantity' => 1,
        ]);

        // Add more quantity to first item
        $this->cartService->addToCart($item1, 2);

        // Update second item quantity
        $this->cartService->updateCartItemQuantity($item2, 3);

        // Check summary
        $summary = $this->cartService->getCartSummary();
        expect($summary->totalItems)->toBe(6); // 3 + 3
        expect($summary->totalPrice)->toBe(1200.0); // (3 * 150) + (3 * 250)

        // Remove one item
        $this->cartService->removeFromCart($item1);

        $summary = $this->cartService->getCartSummary();
        expect($summary->totalItems)->toBe(3);
        expect($summary->totalPrice)->toBe(750.0); // 3 * 250

        // Clear cart
        $this->cartService->clearCart();

        $summary = $this->cartService->getCartSummary();
        expect($summary->totalItems)->toBe(0);
        expect($summary->totalPrice)->toBe(0.0);
    });
});

describe('toOrderItems', function () {
    it('converts all cart items to order items successfully', function () {
        // Product 1: Has sale price on product, variant has sale price (variant sale price should win)
        $product1 = Product::factory()->create([
            'price' => 150,
            'sale_price' => 130,
        ]);
        // Product 2: No sale price on product, variant has sale price (variant sale price should be used)
        $product2 = Product::factory()->create([
            'price' => 250,
            'sale_price' => null,
        ]);

        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'price' => 120,
            'sale_price' => 100, // This should be the final price
            'quantity' => 10,
        ]);
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'price' => 200,
            'sale_price' => 180, // This should be the final price
            'quantity' => 5,
        ]);

        // Create cart with items
        $cart = $this->cartService->getOrCreateCart();
        $cartItem1 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'product_variant_id' => $variant1->id,
            'quantity' => 2,
        ]);
        $cartItem2 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'product_variant_id' => $variant2->id,
            'quantity' => 3,
        ]);

        // Create an order
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Verify cart has items before conversion
        expect($cart->items)->toHaveCount(2);
        expect($order->items)->toHaveCount(0);

        // Convert cart items to order items
        $this->cartService->toOrderItems($order);

        // Verify order items were created
        $order->refresh();
        expect($order->items)->toHaveCount(2);

        // Verify first order item uses variant sale price
        $orderItem1 = $order->items->where('product_id', $product1->id)->first();
        expect($orderItem1)->not->toBeNull();
        expect($orderItem1->product_id)->toBe($product1->id);
        expect($orderItem1->variant_id)->toBe($variant1->id);
        expect($orderItem1->quantity)->toBe(2);
        expect((float) $orderItem1->unit_price)->toBe(100.0); // Variant sale price
        expect((float) $orderItem1->subtotal)->toBe(200.0); // 100 * 2

        // Verify second order item uses variant sale price
        $orderItem2 = $order->items->where('product_id', $product2->id)->first();
        expect($orderItem2)->not->toBeNull();
        expect($orderItem2->product_id)->toBe($product2->id);
        expect($orderItem2->variant_id)->toBe($variant2->id);
        expect($orderItem2->quantity)->toBe(3);
        expect((float) $orderItem2->unit_price)->toBe(180.0); // Variant sale price
        expect((float) $orderItem2->subtotal)->toBe(540.0); // 180 * 3

        // Verify inventory was updated
        $variant1->refresh();
        $variant2->refresh();
        expect($variant1->quantity)->toBe(8); // 10 - 2
        expect($variant2->quantity)->toBe(2); // 5 - 3
    });

    it('handles empty cart when converting to order items', function () {
        // Create an empty cart
        $cart = $this->cartService->getOrCreateCart();
        expect($cart->items)->toHaveCount(0);

        // Create an order
        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Convert empty cart to order items (should not create any)
        $this->cartService->toOrderItems($order);

        // Verify no order items were created
        $order->refresh();
        expect($order->items)->toHaveCount(0);
    });

    it('converts cart items with sale prices correctly', function () {
        $product = Product::factory()->create([
            'price' => 100,
            'sale_price' => 80,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 90,
            'sale_price' => 75,
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Convert cart items to order items
        $this->cartService->toOrderItems($order);

        // Verify order item uses variant sale price
        $order->refresh();
        $orderItem = $order->items->first();
        expect((float) $orderItem->unit_price)->toBe(75.0); // Variant sale price takes precedence
        expect((float) $orderItem->subtotal)->toBe(150.0); // 75 * 2
    });

    it('throws exception when cart item has no variant', function () {
        $product = Product::factory()->create(['price' => 100]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null, // No variant
            'quantity' => 1,
        ]);

        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Should throw exception when trying to convert cart item without variant
        expect(function () use ($order) {
            $this->cartService->toOrderItems($order);
        })->toThrow(\Exception::class, 'Cart item must have a variant');
    });

    it('uses product sale price when variant has no sale price', function () {
        $product = Product::factory()->create([
            'price' => 100,
            'sale_price' => 85,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 90,
            'sale_price' => null, // No variant sale price
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Convert cart items to order items
        $this->cartService->toOrderItems($order);

        // Verify order item uses product sale price (since variant has no sale price)
        $order->refresh();
        $orderItem = $order->items->first();
        expect((float) $orderItem->unit_price)->toBe(90.0); // ProductVariant sale price
        expect((float) $orderItem->subtotal)->toBe(180.0); // 85 * 2
    });

    it('uses variant price when no sale prices are available', function () {
        $product = Product::factory()->create([
            'price' => 100,
            'sale_price' => null,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 90,
            'sale_price' => null,
            'quantity' => 10,
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);

        // Convert cart items to order items
        $this->cartService->toOrderItems($order);

        // Verify order item uses variant price (no sale prices available)
        $order->refresh();
        $orderItem = $order->items->first();
        expect((float) $orderItem->unit_price)->toBe(90.0); // Variant price
        expect((float) $orderItem->subtotal)->toBe(180.0); // 90 * 2
    });
});
