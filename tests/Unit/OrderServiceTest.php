<?php

use App\DTOs\OrderPlacementData;
use App\DTOs\OrderEvaluationData;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Area;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Gov;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\ShippingCost;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderEvaluationService;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->orderService = app(OrderService::class);
    $this->cartService = app(CartService::class);
    $this->orderEvaluationService = app(OrderEvaluationService::class);
    Auth::login($this->user);
});

describe('placeOrderFromCart', function () {
    it('successfully places an order from cart', function () {
        // Setup: Create necessary data
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);

        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $orderData = new OrderPlacementData(
            addressId: $address->id,
            paymentMethod: PaymentMethod::CASH_ON_DELIVERY,
            notes: 'Test order notes'
        );

        $order = $this->orderService->placeOrderFromCart($orderData);

        expect($order)->toBeInstanceOf(Order::class);
        expect($order->user_id)->toBe($this->user->id);
        expect($order->order_status)->toBe(OrderStatus::PROCESSING);
        expect($order->payment_status)->toBe(PaymentStatus::PENDING);
        expect($order->payment_method)->toBe(PaymentMethod::CASH_ON_DELIVERY);
        expect($order->notes)->toBe('Test order notes');
        expect($order->shipping_address_id)->toBe($address->id);

        // Verify order items were created
        expect($order->items)->toHaveCount(1);
        expect($order->items->first()->product_id)->toBe($product->id);
        expect($order->items->first()->variant_id)->toBe($variant->id);
        expect($order->items->first()->quantity)->toBe(2);

        // Verify cart was cleared
        $cart = $this->cartService->getCart();
        expect($cart->items)->toHaveCount(0);
    });

    it('places order with coupon code applied', function () {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);

        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $orderData = new OrderPlacementData(
            addressId: $address->id,
            paymentMethod: PaymentMethod::CARD,
            couponCode: 'SAVE10'
        );

        $order = $this->orderService->placeOrderFromCart($orderData);

        expect($order->coupon_code)->toBe('SAVE10');
        expect($order->payment_method)->toBe(PaymentMethod::CARD);
    });

    it('throws exception when cart is empty', function () {
        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $orderData = new OrderPlacementData(
            addressId: $address->id,
            paymentMethod: PaymentMethod::CASH_ON_DELIVERY
        );

        expect(function () use ($orderData) {
            $this->orderService->placeOrderFromCart($orderData);
        })->toThrow(Exception::class, 'Cannot perform this action with an empty cart');
    });

    it('throws exception when address does not exist', function () {
        $product = Product::factory()->create(['price' => 100]);
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $orderData = new OrderPlacementData(
            addressId: 999, // Non-existent address
            paymentMethod: PaymentMethod::CASH_ON_DELIVERY
        );

        expect(function () use ($orderData) {
            $this->orderService->placeOrderFromCart($orderData);
        })->toThrow(ModelNotFoundException::class);
    });

    it('throws exception when user is not authenticated', function () {
        Auth::logout();

        $address = Address::factory()->create();
        $orderData = new OrderPlacementData(
            addressId: $address->id,
            paymentMethod: PaymentMethod::CASH_ON_DELIVERY
        );

        expect(function () use ($orderData) {
            $this->orderService->placeOrderFromCart($orderData);
        })->toThrow(Exception::class, 'User must be authenticated to perform this action');
    });
});

describe('getOrderById', function () {
    it('returns order with all relationships loaded', function () {
        $product = Product::factory()->create(['price' => 150]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $address->id
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 150,
            'subtotal' => 300
        ]);

        $result = $this->orderService->getOrderById($order->id);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->id)->toBe($order->id);
        expect($result->items)->toHaveCount(1);
        expect($result->shippingAddress)->toBeInstanceOf(Address::class);

        // Check that relationships are loaded
        expect($result->relationLoaded('items'))->toBeTrue();
        expect($result->relationLoaded('shippingAddress'))->toBeTrue();
        expect($result->items->first()->relationLoaded('product'))->toBeTrue();
        expect($result->items->first()->relationLoaded('variant'))->toBeTrue();
        expect($result->shippingAddress->relationLoaded('area'))->toBeTrue();
    });

    it('throws exception when order does not exist', function () {
        expect(function () {
            $this->orderService->getOrderById(999);
        })->toThrow(ModelNotFoundException::class);
    });

    it('throws exception when order belongs to different user', function () {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        expect(function () use ($order) {
            $this->orderService->getOrderById($order->id);
        })->toThrow(ModelNotFoundException::class);
    });

    it('throws exception when user is not authenticated', function () {
        Auth::logout();
        $order = Order::factory()->create();

        expect(function () use ($order) {
            $this->orderService->getOrderById($order->id);
        })->toThrow(Exception::class, 'User must be authenticated to perform this action');
    });
});

describe('getUserOrders', function () {
    it('returns paginated orders for current user', function () {
        // Create orders for current user
        $orders = Order::factory()->count(15)->create(['user_id' => $this->user->id]);

        // Create orders for another user (should not be included)
        $otherUser = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $otherUser->id]);

        $result = $this->orderService->getUserOrders(10);

        expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        expect($result->total())->toBe(15);
        expect($result->perPage())->toBe(10);
        expect($result->items())->toHaveCount(10);

        // Verify all orders belong to current user
        foreach ($result->items() as $order) {
            expect($order->user_id)->toBe($this->user->id);
        }
    });

    it('returns orders sorted by creation date descending', function () {
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(3)
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(1)
        ]);

        $order3 = Order::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2)
        ]);

        $result = $this->orderService->getUserOrders();

        $orderIds = collect($result->items())->pluck('id')->toArray();
        expect($orderIds)->toBe([$order2->id, $order3->id, $order1->id]);
    });

    it('returns empty result when user has no orders', function () {
        $result = $this->orderService->getUserOrders();

        expect($result->total())->toBe(0);
        expect($result->items())->toHaveCount(0);
    });

    it('throws exception when user is not authenticated', function () {
        Auth::logout();

        expect(function () {
            $this->orderService->getUserOrders();
        })->toThrow(Exception::class, 'User must be authenticated to perform this action');
    });
});

describe('Order Service Integration', function () {
    it('handles complete order workflow', function () {
        // Setup products and address
        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 200]);

        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10
        ]);
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 5
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 25.0
        ]);

        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'product_variant_id' => $variant1->id,
            'quantity' => 2
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'product_variant_id' => $variant2->id,
            'quantity' => 1
        ]);

        // Place order
        $orderData = new OrderPlacementData(
            addressId: $address->id,
            paymentMethod: PaymentMethod::CASH_ON_DELIVERY,
            notes: 'Integration test order'
        );

        $order = $this->orderService->placeOrderFromCart($orderData);

        // Verify order was created correctly
        expect($order->items)->toHaveCount(2);
        expect($order->notes)->toBe('Integration test order');

        // Get order by ID
        $retrievedOrder = $this->orderService->getOrderById($order->id);
        expect($retrievedOrder->id)->toBe($order->id);

        // Get user orders
        $userOrders = $this->orderService->getUserOrders();
        expect($userOrders->total())->toBe(1);
        expect($userOrders->items()[0]->id)->toBe($order->id);

        // Verify inventory was restored
        $variant1->refresh();
        $variant2->refresh();
        expect($variant1->quantity)->toBe(8); // No change as we didn't reduce stock in this test
        expect($variant2->quantity)->toBe(4);  // No change as we didn't reduce stock in this test

        // Verify cart is still empty after order placement
        $cart = $this->cartService->getCart();
        expect($cart->items)->toHaveCount(0);
    });
});
