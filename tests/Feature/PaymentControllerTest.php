<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('handles payment success when payment already processed via webhook', function () {
    // Create an order that's already paid (as if processed via webhook)
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_method' => PaymentMethod::CREDIT_CARD,
        'payment_status' => PaymentStatus::PAID,
        'payment_id' => 'TX-12345',
        'order_status' => OrderStatus::PROCESSING,
    ]);

    // Set order ID in session (as would be done during payment initiation)
    session()->put('payment_order_id', $order->id);

    // Simulate success URL redirect with parameters
    $response = $this->get(route('payment.success', [
        'merchantOrderId' => 'Moda-'.$order->id,
        'transactionId' => 'TX-12345',
        'status' => 'SUCCESS',
    ]));

    // Should redirect to order show page with success message
    $response->assertRedirect(route('orders.show', $order->id));
    $response->assertSessionHas('success', 'Payment completed successfully! Your order is being processed.');

    // Session should be cleared
    expect(session()->has('payment_order_id'))->toBeFalse();
});

it('handles payment success when payment not yet processed', function () {
    // Create an order that's not yet paid
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_method' => PaymentMethod::CREDIT_CARD,
        'payment_status' => PaymentStatus::PENDING,
        'order_status' => OrderStatus::PROCESSING,
    ]);

    // Set order ID in session
    session()->put('payment_order_id', $order->id);

    // Simulate success URL redirect with parameters
    $response = $this->get(route('payment.success', [
        'merchantOrderId' => 'Moda-'.$order->id,
        'transactionId' => 'TX-12345',
        'status' => 'SUCCESS',
    ]));

    // Should redirect to order show page
    $response->assertRedirect(route('orders.show', $order->id));
    $response->assertSessionHas('success', 'Payment completed successfully! Your order is being processed.');

    // Order should now be marked as paid
    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::PAID);
    expect($order->payment_id)->toBe('TX-12345');
});

it('handles payment failure gracefully', function () {
    // Create an order
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_method' => PaymentMethod::CREDIT_CARD,
        'payment_status' => PaymentStatus::PENDING,
    ]);

    // Set order ID in session
    session()->put('payment_order_id', $order->id);

    // Simulate failure URL redirect
    $response = $this->get(route('payment.failure', [
        'error' => 'Payment failed',
    ]));

    // Should redirect back to checkout with error
    $response->assertRedirect(route('checkout.index'));
    $response->assertSessionHas('error');

    // Session should be cleared
    expect(session()->has('payment_order_id'))->toBeFalse();
});

it('handles missing session data gracefully', function () {
    // Don't set any session data

    // Try to access success page
    $response = $this->get(route('payment.success'));

    // Should redirect to checkout with error
    $response->assertRedirect(route('checkout.index'));
    $response->assertSessionHas('error', 'Payment information not found. Please try again.');
});
