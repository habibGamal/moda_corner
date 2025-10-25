<?php

use App\Models\Address;
use App\Models\Area;
use App\Models\CartItem;
use App\Models\Gov;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create address with related gov and area
    $gov = Gov::factory()->create();
    $area = Area::factory()->create(['gov_id' => $gov->id]);
    $this->address = Address::factory()->create([
        'user_id' => $this->user->id,
        'area_id' => $area->id,
    ]);

    // Create product with variant and add to cart
    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'quantity' => 10,
    ]);

    CartItem::factory()->create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
    ]);
});

test('order confirmation email is sent to customer when order is placed', function () {
    Notification::fake();

    $response = $this->actingAs($this->user)->post(route('orders.store'), [
        'address_id' => $this->address->id,
        'payment_method' => 'cod',
        'notes' => 'Test order',
    ]);

    $response->assertRedirect();

    // Assert notification was sent to the user
    Notification::assertSentTo(
        $this->user,
        OrderConfirmationNotification::class
    );
});

test('order confirmation email contains order details', function () {
    Notification::fake();

    $this->actingAs($this->user)->post(route('orders.store'), [
        'address_id' => $this->address->id,
        'payment_method' => 'cod',
        'notes' => 'Test order with notes',
    ]);

    Notification::assertSentTo(
        $this->user,
        OrderConfirmationNotification::class,
        function ($notification, $channels) {
            $order = $notification->order;

            return $order->user_id === $this->user->id &&
                   $order->notes === 'Test order with notes' &&
                   $order->items->count() > 0;
        }
    );
});

test('order confirmation email is queued', function () {
    $this->actingAs($this->user)->post(route('orders.store'), [
        'address_id' => $this->address->id,
        'payment_method' => 'cod',
    ]);

    // Check that jobs were pushed to the queue
    $this->assertDatabaseHas('jobs', [
        'queue' => 'default',
    ]);
});
