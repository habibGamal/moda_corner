<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnOrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->address = Address::factory()->create(['user_id' => $this->user->id]);

    // Create a delivered order for testing
    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_status' => OrderStatus::DELIVERED,
        'payment_status' => PaymentStatus::PAID,
        'payment_method' => PaymentMethod::CREDIT_CARD,
        'delivered_at' => Carbon::now()->subDays(5),
        'shipping_address_id' => $this->address->id,
    ]);

    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'quantity' => 100,
    ]);

    $this->orderItem = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'variant_id' => $this->variant->id,
        'quantity' => 3,
        'unit_price' => 50.00,
        'subtotal' => 150.00,
    ]);
});

it('can display return orders index page', function () {
    $this->actingAs($this->user);

    ReturnOrder::factory()->count(2)->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('returns.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Returns/Index')
        ->has('returnOrders', 2)
    );
});

it('can display create return page for eligible order', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('returns.create', $this->order->id));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Returns/Create')
        ->has('order')
        ->has('returnableItems', 1)
    );
});

it('redirects when trying to create return for ineligible order', function () {
    $this->actingAs($this->user);

    // Make order ineligible
    $this->order->update(['order_status' => OrderStatus::PROCESSING]);

    $response = $this->get(route('returns.create', $this->order->id));

    $response->assertRedirect(route('orders.show', $this->order->id));
    $response->assertSessionHas('error', 'This order is not eligible for return.');
});

it('redirects when trying to create return for order with no returnable items', function () {
    $this->actingAs($this->user);

    // Create a return for all items
    $returnOrder = ReturnOrder::factory()->create([
        'order_id' => $this->order->id,
        'user_id' => $this->user->id,
        'status' => ReturnOrderStatus::APPROVED,
    ]);

    \App\Models\ReturnOrderItem::factory()->create([
        'return_order_id' => $returnOrder->id,
        'order_item_id' => $this->orderItem->id,
        'quantity' => 3, // All items
    ]);

    $response = $this->get(route('returns.create', $this->order->id));

    $response->assertRedirect(route('orders.show', $this->order->id));
    $response->assertSessionHas('error', 'No items are eligible for return in this order.');
});

it('can store a return request successfully', function () {
    $this->actingAs($this->user);

    $requestData = [
        'reason' => 'Product was damaged',
        'return_items' => [
            [
                'order_item_id' => $this->orderItem->id,
                'quantity' => 2,
                'reason' => 'Damaged packaging',
            ],
        ],
    ];

    $response = $this->post(route('returns.store', $this->order->id), $requestData);

    // Check that return order was created
    $returnOrder = ReturnOrder::where('order_id', $this->order->id)->first();
    expect($returnOrder)->not()->toBeNull();
    expect($returnOrder->user_id)->toBe($this->user->id);
    expect($returnOrder->reason)->toBe('Product was damaged');
    expect($returnOrder->status)->toBe(ReturnOrderStatus::REQUESTED);

    // Check redirect
    $response->assertRedirect(route('returns.show', $returnOrder->return_number));
    $response->assertSessionHas('success', 'Return request submitted successfully. It will be reviewed by our team.');
});

it('validates return request data', function () {
    $this->actingAs($this->user);

    $requestData = [
        'reason' => '', // Missing reason
        'return_items' => [], // Empty items
    ];

    $response = $this->post(route('returns.store', $this->order->id), $requestData);

    $response->assertSessionHasErrors(['reason', 'return_items']);
});

it('can display return order details', function () {
    $this->actingAs($this->user);

    $returnOrder = ReturnOrder::factory()->create([
        'user_id' => $this->user->id,
        'return_number' => 'RET-TEST123',
    ]);

    $response = $this->get(route('returns.show', 'RET-TEST123'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Returns/Show')
        ->has('returnOrder')
    );
});

it('redirects when trying to view someone else\'s return', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();
    $returnOrder = ReturnOrder::factory()->create([
        'user_id' => $otherUser->id,
        'return_number' => 'RET-OTHER123',
    ]);

    $response = $this->get(route('returns.show', 'RET-OTHER123'));

    $response->assertRedirect(route('returns.index'));
    $response->assertSessionHas('error', 'Return order not found.');
});

it('redirects when trying to view non-existent return', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('returns.show', 'RET-NONEXISTENT'));

    $response->assertRedirect(route('returns.index'));
    $response->assertSessionHas('error', 'Return order not found.');
});

it('requires authentication for all return routes', function () {
    $routes = [
        ['GET', route('returns.index')],
        ['GET', route('returns.create', $this->order->id)],
        ['POST', route('returns.store', $this->order->id)],
        ['GET', route('returns.show', 'RET-TEST123')],
    ];

    foreach ($routes as [$method, $url]) {
        $response = $this->call($method, $url);
        $response->assertRedirect(route('login'));
    }
});

it('prevents creating return for non-owned orders', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();
    $otherOrder = Order::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('returns.create', $otherOrder->id));

    $response->assertNotFound();
});
