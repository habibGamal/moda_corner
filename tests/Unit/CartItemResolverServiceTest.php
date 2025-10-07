<?php

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductVariantNotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartItemResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $this->service = app(CartItemResolverService::class);
});

describe('resolveCartItem', function () {
    it('returns existing cart item when product and variant match', function () {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $existingCartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $result = $this->service->resolveCartItem($this->cart, $product->id, $variant->id);

        expect($result->id)->toBe($existingCartItem->id);
        expect($result->quantity)->toBe(2);
    });

    it('creates new cart item with quantity 0 when no existing item found', function () {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'quantity' => 10,
        ]);

        $result = $this->service->resolveCartItem($this->cart, $product->id, $variant->id);

        expect($result)->toBeInstanceOf(CartItem::class);
        expect($result->cart_id)->toBe($this->cart->id);
        expect($result->product_id)->toBe($product->id);
        expect($result->product_variant_id)->toBe($variant->id);
        expect($result->quantity)->toBe(0);
        expect($result->exists)->toBeFalse(); // Not saved yet
    });

    it('uses default variant when no variant specified', function () {
        $product = Product::factory()->create(['is_active' => true]);
        $defaultVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'is_default' => true,
            'quantity' => 10,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'is_default' => false,
        ]);

        $result = $this->service->resolveCartItem($this->cart, $product->id);
        expect($result->product_variant_id)->toBe($defaultVariant->id);
    });

    it('uses first active variant when no default variant exists', function () {
        $product = Product::factory()->create(['is_active' => true]);
        $firstVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'is_default' => false,
            'quantity' => 10,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'is_default' => false,
        ]);

        $result = $this->service->resolveCartItem($this->cart, $product->id);

        expect($result->product_variant_id)->toBe($firstVariant->id);
    });

    it('throws ProductNotFoundException when product does not exist', function () {
        expect(fn () => $this->service->resolveCartItem($this->cart, 99999))
            ->toThrow(ProductNotFoundException::class);
    });

    it('throws ProductNotFoundException when product is inactive', function () {
        $product = Product::factory()->create(['is_active' => false]);

        expect(fn () => $this->service->resolveCartItem($this->cart, $product->id))
            ->toThrow(ProductNotFoundException::class);
    });

    it('throws ProductVariantNotFoundException when specified variant does not exist', function () {
        $product = Product::factory()->create(['is_active' => true]);

        expect(fn () => $this->service->resolveCartItem($this->cart, $product->id, 99999))
            ->toThrow(ProductVariantNotFoundException::class);
    });

    it('throws ProductVariantNotFoundException when specified variant is inactive', function () {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => false,
        ]);

        expect(fn () => $this->service->resolveCartItem($this->cart, $product->id, $variant->id))
            ->toThrow(ProductVariantNotFoundException::class);
    });

    it('throws ProductVariantNotFoundException when variant belongs to different product', function () {
        $product1 = Product::factory()->create(['is_active' => true]);
        $product2 = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'is_active' => true,
        ]);

        expect(fn () => $this->service->resolveCartItem($this->cart, $product1->id, $variant->id))
            ->toThrow(ProductVariantNotFoundException::class);
    });

    it('throws ProductVariantNotFoundException when product has no active variants', function () {
        $product = Product::factory()->create(['is_active' => true]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'is_active' => false,
        ]);

        expect(fn () => $this->service->resolveCartItem($this->cart, $product->id))
            ->toThrow(ProductVariantNotFoundException::class);
    });
});

describe('toOrderItem', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $this->order = \App\Models\Order::factory()->create(['user_id' => $this->user->id]);
    });

    it('converts cart item to order item with variant price', function () {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => null,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 80.00,
            'sale_price' => null,
            'quantity' => 10,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $orderItem = $this->service->toOrderItem($cartItem, $this->order);

        expect($orderItem)->toBeInstanceOf(\App\Models\OrderItem::class);
        expect($orderItem->product_id)->toBe($product->id);
        expect($orderItem->variant_id)->toBe($variant->id);
        expect($orderItem->quantity)->toBe(3);
        expect((float) $orderItem->unit_price)->toBe(80.00);
        expect((float) $orderItem->subtotal)->toBe(240.00);
        expect($orderItem->order_id)->toBe($this->order->id);

        // Verify inventory was updated
        $variant->refresh();
        expect($variant->quantity)->toBe(7);
    });

    it('converts cart item to order item with product price when variant has no price', function () {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => null,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => null,
            'sale_price' => null,
            'quantity' => 10,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $orderItem = $this->service->toOrderItem($cartItem, $this->order);

        expect((float) $orderItem->unit_price)->toBe(100.00);
        expect((float) $orderItem->subtotal)->toBe(200.00);
    });

    it('uses variant sale price when available', function () {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 90.00,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 80.00,
            'sale_price' => 70.00,
            'quantity' => 10,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $orderItem = $this->service->toOrderItem($cartItem, $this->order);

        expect((float) $orderItem->unit_price)->toBe(70.00);
        expect((float) $orderItem->subtotal)->toBe(70.00);
    });

    it('uses product sale price when variant has no sale price', function () {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 85.00,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 80.00,
            'sale_price' => null,
            'quantity' => 10,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $orderItem = $this->service->toOrderItem($cartItem, $this->order);

        expect((float) $orderItem->unit_price)->toBe(80.00);
        expect((float) $orderItem->subtotal)->toBe(80.00);
    });

    it('throws exception when cart item has no variant', function () {
        $product = Product::factory()->create([
            'price' => 50.00,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 5,
        ]);

        expect(fn () => $this->service->toOrderItem($cartItem, $this->order))
            ->toThrow(\Exception::class, 'Cart item must have a variant to be converted to order item');
    });
});
