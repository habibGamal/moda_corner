<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;

test('authenticated user can submit a review for a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post("/products/{$product->id}/reviews", [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('product_reviews', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => 'Great product!',
    ]);
});

test('guest cannot submit a review', function () {
    $product = Product::factory()->create();

    $this->post("/products/{$product->id}/reviews", [
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Great product!',
    ])
        ->assertRedirect(); // Redirects to login or home based on config
});

test('user cannot submit duplicate reviews for the same product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    ProductReview::factory()->create([
        'product_id' => $product->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post("/products/{$product->id}/reviews", [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Another review',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('review must have a valid rating between 1 and 5', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post("/products/{$product->id}/reviews", [
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => 'Invalid rating',
        ])
        ->assertSessionHasErrors('rating');
});

test('user can update their own review', function () {
    $user = User::factory()->create();
    $review = ProductReview::factory()->create([
        'user_id' => $user->id,
        'rating' => 3,
        'comment' => 'Original comment',
    ]);

    $this->actingAs($user)
        ->put("/reviews/{$review->id}", [
            'product_id' => $review->product_id,
            'rating' => 5,
            'comment' => 'Updated comment',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $review->refresh();
    expect($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Updated comment');
});

test('user cannot update another users review', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = ProductReview::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($user)
        ->put("/reviews/{$review->id}", [
            'product_id' => $review->product_id,
            'rating' => 5,
            'comment' => 'Trying to update',
        ])
        ->assertForbidden();
});

test('user can delete their own review', function () {
    $user = User::factory()->create();
    $review = ProductReview::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->delete("/reviews/{$review->id}")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('product_reviews', [
        'id' => $review->id,
    ]);
});

test('verified purchase flag is set correctly when user purchased the product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    // Create a delivered order with this product
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_status' => 'delivered',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'variant_id' => null,
        'quantity' => 1,
        'unit_price' => $product->price,
        'subtotal' => $product->price,
    ]);

    $this->actingAs($user)
        ->post("/products/{$product->id}/reviews", [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

    $review = ProductReview::where('product_id', $product->id)
        ->where('user_id', $user->id)
        ->first();

    expect($review->is_verified_purchase)->toBeTrue()
        ->and($review->order_id)->not->toBeNull();
});

test('can fetch reviews for a product', function () {
    $product = Product::factory()->create();
    ProductReview::factory()->count(3)->create([
        'product_id' => $product->id,
        'is_approved' => true,
    ]);

    $response = $this->get("/products/{$product->id}/reviews");

    $response->assertOk()
        ->assertJsonStructure([
            'reviews' => [
                'data',
                'current_page',
                'last_page',
                'total',
            ],
            'stats' => [
                'average_rating',
                'total_reviews',
                'rating_breakdown',
            ],
        ]);
});

test('only approved reviews are shown in public listing', function () {
    $product = Product::factory()->create();

    ProductReview::factory()->count(2)->create([
        'product_id' => $product->id,
        'is_approved' => true,
    ]);

    ProductReview::factory()->count(3)->create([
        'product_id' => $product->id,
        'is_approved' => false,
    ]);

    $response = $this->get("/products/{$product->id}/reviews");

    $data = $response->json();
    expect($data['reviews']['total'])->toBe(2);
});
