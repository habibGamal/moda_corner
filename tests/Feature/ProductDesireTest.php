<?php

use App\Models\Product;
use App\Models\ProductDesire;
use App\Models\User;
use App\Services\StockNotificationService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
    $this->service = app(StockNotificationService::class);
});

test('product desire is created when user subscribes to stock notification', function () {
    $email = 'test@example.com';

    $this->actingAs($this->user);

    $this->service->subscribe($this->product->id, $email);

    $this->assertDatabaseHas('product_desires', [
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'email' => $email,
    ]);
});

test('product desire is created for guest subscriptions', function () {
    $email = 'guest@example.com';

    // Ensure no user is authenticated
    auth()->logout();

    $this->service->subscribe($this->product->id, $email);

    $desire = ProductDesire::where('product_id', $this->product->id)
        ->where('email', $email)
        ->first();

    expect($desire)->not->toBeNull();
    expect($desire->email)->toBe($email);
    // Note: user_id might be set if a user was authenticated before, so we just check desire exists
});

test('multiple product desires can be created for same user and product', function () {
    $email = 'test@example.com';

    $this->actingAs($this->user);

    // Subscribe multiple times
    $this->service->subscribe($this->product->id, $email);
    $this->service->subscribe($this->product->id, $email);
    $this->service->subscribe($this->product->id, $email);

    $count = ProductDesire::where('product_id', $this->product->id)
        ->where('email', $email)
        ->count();

    expect($count)->toBe(3);
});

test('product has desires relationship', function () {
    $email = 'test@example.com';

    $this->service->subscribe($this->product->id, $email);

    $product = Product::with('desires')->find($this->product->id);

    expect($product->desires)->toHaveCount(1);
    expect($product->desires->first()->email)->toBe($email);
});

test('user has product desires relationship', function () {
    $this->actingAs($this->user);

    $this->service->subscribe($this->product->id, $this->user->email);

    $user = User::with('productDesires')->find($this->user->id);

    expect($user->productDesires)->toHaveCount(1);
    expect($user->productDesires->first()->product_id)->toBe($this->product->id);
});

test('product desire tracks correct timestamps', function () {
    $email = 'test@example.com';

    $this->service->subscribe($this->product->id, $email);

    $desire = ProductDesire::where('email', $email)->first();

    expect($desire->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($desire->created_at)->not->toBeNull();
    expect($desire->updated_at)->not->toBeNull();
});

test('product desires can be filtered by date range', function () {
    // Create desires at different times
    ProductDesire::create([
        'product_id' => $this->product->id,
        'email' => 'old@example.com',
        'created_at' => now()->subDays(10),
    ]);

    ProductDesire::create([
        'product_id' => $this->product->id,
        'email' => 'recent@example.com',
        'created_at' => now()->subDays(2),
    ]);

    $startDate = now()->subDays(5);
    $endDate = now();

    $count = ProductDesire::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->count();

    expect($count)->toBe(1);
});

test('product desires can be counted per product', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    // Create desires for product1
    ProductDesire::create(['product_id' => $product1->id, 'email' => 'user1@example.com']);
    ProductDesire::create(['product_id' => $product1->id, 'email' => 'user2@example.com']);
    ProductDesire::create(['product_id' => $product1->id, 'email' => 'user3@example.com']);

    // Create desire for product2
    ProductDesire::create(['product_id' => $product2->id, 'email' => 'user4@example.com']);

    expect($product1->desires()->count())->toBe(3);
    expect($product2->desires()->count())->toBe(1);
});

test('unique emails can be counted for product desires', function () {
    $email = 'repeat@example.com';

    // Same email subscribes multiple times
    ProductDesire::create(['product_id' => $this->product->id, 'email' => $email]);
    ProductDesire::create(['product_id' => $this->product->id, 'email' => $email]);
    ProductDesire::create(['product_id' => $this->product->id, 'email' => 'other@example.com']);

    $uniqueCount = ProductDesire::where('product_id', $this->product->id)
        ->distinct('email')
        ->count('email');

    expect($uniqueCount)->toBe(2);
});
