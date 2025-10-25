<?php

use App\Jobs\SendStockNotificationsJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use App\Models\User;
use App\Notifications\BackInStockNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'quantity' => 0,
    ]);
});

test('guest can subscribe to stock notifications', function () {
    $email = 'guest@example.com';

    $response = $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('stock_notifications', [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);
});

test('authenticated user can subscribe to stock notifications', function () {
    $response = $this->actingAs($this->user)->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $this->user->email,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('stock_notifications', [
        'product_id' => $this->product->id,
        'email' => $this->user->email,
        'user_id' => $this->user->id,
    ]);
});

test('duplicate subscriptions are prevented by unique constraint', function () {
    $email = 'test@example.com';

    // First subscription
    $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    // Second subscription with same email and product should update, not create new
    $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    $count = StockNotification::where('product_id', $this->product->id)
        ->where('email', $email)
        ->count();

    expect($count)->toBe(1);
});

test('user can unsubscribe from stock notifications', function () {
    $email = 'test@example.com';

    // Subscribe first
    $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    // Then unsubscribe
    $response = $this->post(route('stock-notifications.unsubscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('stock_notifications', [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);
});

test('subscription status can be checked', function () {
    $email = 'test@example.com';

    // Check before subscription
    $response = $this->get(route('stock-notifications.check', [
        'product_id' => $this->product->id,
        'email' => $email,
    ]));

    $response->assertJson(['subscribed' => false]);

    // Subscribe
    $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    // Check after subscription
    $response = $this->get(route('stock-notifications.check', [
        'product_id' => $this->product->id,
        'email' => $email,
    ]));

    $response->assertJson(['subscribed' => true]);
});

test('job is dispatched when variant stock increases from 0', function () {
    Queue::fake();

    $this->variant->update(['quantity' => 5]);

    Queue::assertPushed(SendStockNotificationsJob::class, function ($job) {
        return $job->productId === $this->product->id;
    });
});

test('job is not dispatched when stock increases but was not 0', function () {
    // Set initial quantity > 0 before faking queue
    $this->variant->update(['quantity' => 5]);

    Queue::fake();

    // Increase quantity
    $this->variant->update(['quantity' => 10]);

    Queue::assertNothingPushed();
});

test('job is not dispatched when stock decreases', function () {
    // Set initial quantity > 0 before faking queue
    $this->variant->update(['quantity' => 10]);

    Queue::fake();

    // Decrease quantity to 0
    $this->variant->update(['quantity' => 0]);

    Queue::assertNothingPushed();
});

test('notifications are sent to all pending subscribers when stock available', function () {
    Notification::fake();

    // Create multiple subscriptions
    $email1 = 'user1@example.com';
    $email2 = 'user2@example.com';

    StockNotification::create([
        'product_id' => $this->product->id,
        'email' => $email1,
    ]);

    StockNotification::create([
        'product_id' => $this->product->id,
        'email' => $email2,
    ]);

    // Execute the job
    $job = new SendStockNotificationsJob($this->product->id);
    $job->handle();

    // Assert notifications were sent
    Notification::assertSentTimes(BackInStockNotification::class, 2);
});

test('notifications are marked as notified after sending', function () {
    $email = 'test@example.com';

    $notification = StockNotification::create([
        'product_id' => $this->product->id,
        'email' => $email,
    ]);

    expect($notification->notified_at)->toBeNull();

    // Execute the job
    $job = new SendStockNotificationsJob($this->product->id);
    $job->handle();

    $notification->refresh();
    expect($notification->notified_at)->not->toBeNull();
});

test('already notified subscribers do not receive duplicate notifications', function () {
    Notification::fake();

    $email = 'test@example.com';

    StockNotification::create([
        'product_id' => $this->product->id,
        'email' => $email,
        'notified_at' => now(),
    ]);

    // Execute the job
    $job = new SendStockNotificationsJob($this->product->id);
    $job->handle();

    // No notifications should be sent
    Notification::assertNothingSent();
});

test('subscription requires valid email', function () {
    $response = $this->post(route('stock-notifications.subscribe'), [
        'product_id' => $this->product->id,
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('subscription requires existing product', function () {
    $response = $this->post(route('stock-notifications.subscribe'), [
        'product_id' => 99999,
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHasErrors('product_id');
});
