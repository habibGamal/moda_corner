<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\Address;
use App\Models\Area;
use App\Models\Gov;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Carbon\Carbon;

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

    // Create products
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

describe('requestReturn', function () {
    it('successfully requests return for eligible order', function () {
        // Create a delivered order that is eligible for return
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'payment_status' => PaymentStatus::PAID,
            'delivered_at' => Carbon::now()->subDays(5), // Within 14 days
            'return_status' => null,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        $returnReason = 'Product is defective and not as described';

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => $returnReason,
        ]);

        $response->assertRedirect(route('orders.show', $order->id))
            ->assertSessionHas('success', 'تم إرسال طلب الإرجاع بنجاح. سيتم مراجعته من قبل الإدارة.');

        // Verify the order was updated
        $order->refresh();
        expect($order->return_status)->toBe(ReturnStatus::RETURN_REQUESTED);
        expect($order->return_reason)->toBe($returnReason);
        expect($order->return_requested_at)->not->toBeNull();
    });

    it('fails validation with short reason', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(5),
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'short', // Less than 10 characters
        ]);

        $response->assertSessionHasErrors(['reason']);
    });

    it('fails validation with long reason', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(5),
            'return_status' => null,
        ]);

        $longReason = str_repeat('a', 501); // More than 500 characters

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => $longReason,
        ]);

        $response->assertSessionHasErrors(['reason']);
    });

    it('fails validation without reason', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(5),
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), []);

        $response->assertSessionHasErrors(['reason']);
    });

    it('rejects return request for non-delivered order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::PROCESSING, // Not delivered
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.show', $order->id))
            ->assertSessionHas('error');

        // Verify the order was not updated
        $order->refresh();
        expect($order->return_status)->toBeNull();
        expect($order->return_reason)->toBeNull();
    });

    it('rejects return request for order older than 14 days', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(15), // More than 14 days
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.show', $order->id))
            ->assertSessionHas('error');

        // Verify the order was not updated
        $order->refresh();
        expect($order->return_status)->toBeNull();
    });

    it('rejects return request for already returned order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(5),
            'return_status' => ReturnStatus::RETURN_REQUESTED, // Already has return status
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.show', $order->id))
            ->assertSessionHas('error');
    });

    it('rejects return request for order without delivered_at date', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => null, // No delivery date set
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.show', $order->id))
            ->assertSessionHas('error');
    });

    it('rejects return request for order that does not belong to user', function () {
        $otherUser = User::factory()->create();
        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $this->area->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $otherUser->id, // Different user
            'shipping_address_id' => $otherAddress->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => Carbon::now()->subDays(5),
            'return_status' => null,
        ]);

        $response = $this->post(route('orders.return.request', $order->id), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.index'))
            ->assertSessionHas('error', 'الطلب غير موجود.');
    });

    it('rejects return request for non-existent order', function () {
        $response = $this->post(route('orders.return.request', 99999), [
            'reason' => 'Product is defective and not as described',
        ]);

        $response->assertRedirect(route('orders.index'))
            ->assertSessionHas('error', 'الطلب غير موجود.');
    });
});

describe('history', function () {
    it('displays return history for authenticated user', function () {
        // Create orders with different return statuses
        $returnedOrder1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => Carbon::now()->subDays(2),
            'return_reason' => 'Product defective',
        ]);

        $returnedOrder2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'return_requested_at' => Carbon::now()->subDays(5),
            'return_reason' => 'Wrong size',
        ]);        // Create order items for the returned orders
        OrderItem::factory()->create([
            'order_id' => $returnedOrder1->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $returnedOrder2->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ]);

        // Create an order without return status (should not appear)
        Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'return_status' => null,
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
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        $response = $this->get(route('orders.returns.history'));

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Orders/ReturnHistory')
                ->has('returnHistory', 2)
                ->where('returnHistory.0.id', $returnedOrder1->id) // Most recent first
                ->where('returnHistory.1.id', $returnedOrder2->id)
        );
    });

    it('displays empty return history when no returns exist', function () {
        // Create an order without return status
        Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'order_status' => OrderStatus::DELIVERED,
            'return_status' => null,
        ]);
        $response = $this->get(route('orders.returns.history'));

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Orders/ReturnHistory')
                ->has('returnHistory', 0)
        );
    });

    it('orders return history by return_requested_at descending', function () {
        $firstReturn = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => Carbon::now()->subDays(10),
        ]);

        $secondReturn = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'return_requested_at' => Carbon::now()->subDays(5),
        ]);

        $thirdReturn = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'return_status' => ReturnStatus::REFUND_PROCESSED,
            'return_requested_at' => Carbon::now()->subDays(2),
        ]);
        $response = $this->get(route('orders.returns.history'));

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Orders/ReturnHistory')
                ->has('returnHistory', 3)
                ->where('returnHistory.0.id', $thirdReturn->id) // Most recent first
                ->where('returnHistory.1.id', $secondReturn->id)
                ->where('returnHistory.2.id', $firstReturn->id) // Oldest last
        );
    });

    it('includes related order items with products and variants', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $this->address->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => Carbon::now()->subDays(2),
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ]);
        $response = $this->get(route('orders.returns.history'));

        $response->assertInertia(
            fn(AssertableInertia $page) => $page
                ->component('Orders/ReturnHistory')
                ->has('returnHistory', 1)
                ->has('returnHistory.0.items', 1)
                ->has('returnHistory.0.items.0.product')
                ->has('returnHistory.0.items.0.variant')
                ->where('returnHistory.0.items.0.product.id', $this->product->id)
                ->where('returnHistory.0.items.0.variant.id', $this->variant->id)
        );
    });
});
