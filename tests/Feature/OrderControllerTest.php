<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Area;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Gov;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create address structure
    $this->gov = Gov::factory()->create();
    $this->area = Area::factory()->create(['gov_id' => $this->gov->id]);
    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'area_id' => $this->area->id,
    ]);

    // Create shipping cost for the area
    $this->shippingCost = ShippingCost::factory()->create([
        'area_id' => $this->area->id,
        'value' => 25.00,
    ]);

    // Create products and cart for checkout tests
    $this->product = Product::factory()->create([
        'name_en' => 'Test Product',
        'name_ar' => 'منتج اختبار',
        'price' => 100.00,
        'is_active' => true,
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'is_active' => true,
        'is_default' => true,
        'quantity' => 20,
    ]);
});

describe('index', function () {
    it('displays user orders with pagination', function () {
        // Create multiple orders for the user
        $orders = Order::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
        ]);

        // Create an order for another user (should not appear)
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $this->area->id,
        ]);
        Order::factory()->create([
            'user_id' => $otherUser->id,
            'shipping_address_id' => $otherAddress->id,
        ]);

        $response = $this->get(route('orders.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders_data', 5)
            ->has('orders_pagination')
            ->where('orders_pagination.total', 5)
        );
    });

    it('respects pagination limit parameter', function () {
        Order::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('orders.index', ['limit' => 5]));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders_data', 5)
            ->where('orders_pagination.per_page', 5)
            ->where('orders_pagination.total', 15)
        );
    });

    it('uses default limit when not specified', function () {
        Order::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
        ]);

        $response = $this->get(route('orders.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders_data', 10) // Default limit is 10
            ->where('orders_pagination.per_page', 10)
        );
    });
});

describe('show', function () {
    it('displays order details for valid order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'total' => 125.00,
            'order_status' => OrderStatus::PROCESSING,
            'payment_status' => PaymentStatus::PAID,
        ]);

        $response = $this->get(route('orders.show', $order->id));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Show')
            ->has('order')
            ->where('order.id', $order->id)
            ->where('order.total', '125.00')
            ->where('order.order_status', 'processing')
            ->where('order.payment_status', 'paid')
        );
    });

    it('redirects when order not found', function () {
        $response = $this->get(route('orders.show', 999999));

        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('error', 'Order not found.');
    });

    it('allows user to view their own orders only', function () {
        // Create an order for another user
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $this->area->id,
        ]);
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'shipping_address_id' => $otherAddress->id,
        ]);

        $response = $this->get(route('orders.show', $otherOrder->id));

        // Should redirect because the order doesn't belong to the current user
        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('error', 'Order not found.');
    });
});

describe('checkout', function () {
    it('displays checkout page with addresses and cart data', function () {
        // Create additional address
        $secondAddress = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $this->area->id,
        ]);

        // Create cart with items
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $response = $this->get(route('checkout.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Index')
            ->has('addresses', 2)
            ->has('cartSummary')
            ->has('paymentMethods')
            ->where('cartSummary.totalItems', 2)
            ->where('cartSummary.totalPrice', fn($value) => (float)$value === 200.0)
            ->where('paymentMethods', ['cash_on_delivery', 'credit_card', 'wallet'])
            ->where('orderSummary', null) // No address selected initially
        );
    });

    it('calculates order summary when address is selected', function () {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);
        $response = $this->get(route('checkout.index', ['address_id' => $this->address->id]));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Index')
            ->has('orderSummary')
            ->where('orderSummary.subtotal', fn ($value) => abs($value - 100.0) < 0.01)
            ->where('orderSummary.shippingCost', fn ($value) => abs($value - 25.0) < 0.01)
            ->where('orderSummary.total', fn ($value) => abs($value - 125.0) < 0.01)
        );
    });

    it('redirects to cart when cart is empty', function () {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'))
                ->assertSessionHas('error', 'Your cart is empty. Please add items to your cart before checking out.');
    });
});

describe('store', function () {
    beforeEach(function () {
        // Create cart with items for order placement
        $this->cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $this->cartItem = CartItem::factory()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);
    });

    it('creates order with cash on delivery payment', function () {
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Test order notes',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $order = Order::where('user_id', $this->user->id)->first();

        expect($order)->not->toBeNull();
        expect($order->payment_method)->toBe(PaymentMethod::CASH_ON_DELIVERY);
        expect($order->payment_status)->toBe(PaymentStatus::PENDING);
        expect($order->notes)->toBe('Test order notes');
        expect($order->shipping_address_id)->toBe($this->address->id);

        $response->assertRedirect(route('orders.show', $order->id))
                ->assertSessionHas('success', 'Order placed successfully!');
    });

    it('redirects to payment initiation for credit_card payment', function () {
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'credit_card',
            'notes' => 'credit_card payment order',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $order = Order::where('user_id', $this->user->id)->first();

        expect($order)->not->toBeNull();
        expect($order->payment_method)->toBe(PaymentMethod::CREDIT_CARD);
        expect($order->payment_status)->toBe(PaymentStatus::PENDING);

        $response->assertRedirect(route('payment.initiate', ['order_id' => $order->id]));
    });

    it('validates required fields', function () {
        $response = $this->post(route('orders.store'), []);

        $response->assertSessionHasErrors(['address_id', 'payment_method']);
    });

    it('validates address belongs to user', function () {
        $otherUser = User::factory()->create();
        $otherArea = Area::factory()->create(['gov_id' => $this->gov->id]);
        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $otherArea->id,
        ]);

        $orderData = [
            'address_id' => $otherAddress->id,
            'payment_method' => 'cash_on_delivery',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $response->assertSessionHasErrors(['address_id']);
    });

    it('validates payment method', function () {
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'invalid_method',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $response->assertSessionHasErrors(['payment_method']);
    });

    it('handles address not found error', function () {
        $orderData = [
            'address_id' => 999999,
            'payment_method' => 'cash_on_delivery',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $response->assertSessionHasErrors(['address_id']);
    });

    it('creates order with optional coupon code', function () {
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'cash_on_delivery',
            'coupon_code' => 'TESTCOUPON',
            'notes' => 'Order with coupon',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $order = Order::where('user_id', $this->user->id)->first();

        expect($order->coupon_code)->toBe('TESTCOUPON');
        expect($order->notes)->toBe('Order with coupon');

        $response->assertRedirect(route('orders.show', $order->id));
    });

    it('validates notes length', function () {
        $longNotes = str_repeat('a', 1001); // Exceeds 1000 character limit

        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => $longNotes,
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $response->assertSessionHasErrors(['notes']);
    });
});

describe('cancel', function () {
    it('cancels order successfully', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        $response = $this->patch(route('orders.cancel', $order->id));

        $order->refresh();
        expect($order->order_status)->toBe(OrderStatus::CANCELLED);

        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('success', 'Order cancelled successfully.');
    });    it('handles order not found error', function () {
        $response = $this->patch(route('orders.cancel', 999999));

        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('error', 'Order not found.');
    });

    it('prevents cancelling other users orders', function () {
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $this->area->id,
        ]);
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'shipping_address_id' => $otherAddress->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        $response = $this->patch(route('orders.cancel', $otherOrder->id));

        $response->assertRedirect(route('orders.index'))
                ->assertSessionHas('error', 'Order not found.');        // Verify order was not cancelled
        $otherOrder->refresh();
        expect($otherOrder->order_status)->toBe(OrderStatus::PROCESSING);
    });

    it('prevents cancelling orders that are not in processing status', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
        ]);

        $response = $this->patch(route('orders.cancel', $order->id));

        $response->assertRedirect()
                ->assertSessionHas('error');

        // Verify order status was not changed
        $order->refresh();
        expect($order->order_status)->toBe(OrderStatus::DELIVERED);
    });
});

describe('Order Flow Integration', function () {
    it('completes full order placement flow with cash on delivery', function () {
        // Step 1: Create cart with items
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        // Step 2: View checkout page
        $checkoutResponse = $this->get(route('checkout.index', ['address_id' => $this->address->id]));        $checkoutResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Index')
            ->has('orderSummary')
            ->where('orderSummary.total', function ($value) {
                return (float) $value === 125.0;
            })
        );

        // Step 3: Place order
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Integration test order',
        ];

        $storeResponse = $this->post(route('orders.store'), $orderData);

        $order = Order::where('user_id', $this->user->id)->first();
        expect($order)->not->toBeNull();
        expect($order->total)->toBe('125.00');
        expect($order->payment_method)->toBe(PaymentMethod::CASH_ON_DELIVERY);

        $storeResponse->assertRedirect(route('orders.show', $order->id));

        // Step 4: View order details
        $showResponse = $this->get(route('orders.show', $order->id));
        $showResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Show')
            ->where('order.id', $order->id)
            ->where('order.total', '125.00')
        );

        // Step 5: View orders list
        $indexResponse = $this->get(route('orders.index'));
        $indexResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders_data', 1)
        );
    });

    it('completes credit_card payment flow integration', function () {
        // Create cart
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        // Place order with credit_card payment
        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'credit_card',
            'notes' => 'credit_card integration test',
        ];

        $response = $this->post(route('orders.store'), $orderData);

        $order = Order::where('user_id', $this->user->id)->first();
        expect($order)->not->toBeNull();
        expect($order->payment_method)->toBe(PaymentMethod::CREDIT_CARD);
        expect($order->payment_status)->toBe(PaymentStatus::PENDING);

        // Should redirect to payment initiation
        $response->assertRedirect(route('payment.initiate', ['order_id' => $order->id]));
    });

    it('handles order cancellation flow', function () {
        // Create and place order
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $orderData = [
            'address_id' => $this->address->id,
            'payment_method' => 'cash_on_delivery',
        ];

        $this->post(route('orders.store'), $orderData);
        $order = Order::where('user_id', $this->user->id)->first();        // Cancel the order
        $cancelResponse = $this->patch(route('orders.cancel', $order->id));
        $cancelResponse->assertRedirect(route('orders.index'))
                      ->assertSessionHas('success');

        // Verify cancellation
        $order->refresh();
        expect($order->order_status)->toBe(OrderStatus::CANCELLED);

        // Check it appears in orders list as cancelled
        $indexResponse = $this->get(route('orders.index'));
        $indexResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Orders/Index')
            ->has('orders_data', 1)
        );
    });
});
