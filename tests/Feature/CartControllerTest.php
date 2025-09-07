<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user and authenticate
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create a product with a variant
    $this->product = Product::factory()->create([
        'name_en' => 'Test Product',
        'name_ar' => 'منتج اختبار',
        'price' => 100.00,
        'is_active' => true,
    ]);

    // Create a product variant
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'is_active' => true,
        'is_default' => true,
        'quantity' => 20,
    ]);
});

test('can view cart page', function () {
    // Create a cart for the user
    $cart = Cart::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Add an item to the cart
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    // Visit the cart page
    $response = $this->get(route('cart.index'));    // Assert that the page renders with the correct data
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Cart/Index')
        ->has('cart')
        ->has('cart.items', 1)
        ->has('cartSummary')
        ->where('cartSummary.totalItems', 2)
        // Use a callback to compare float values to avoid precision issues
        ->where('cartSummary.totalPrice', 200)
    );
});

test('can add item to cart', function () {
    // Make request to add item to cart
    $response = $this->postJson(route('cart.add'), [
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 3,
    ]);

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_summary' => [
                'totalItems' => 3,
                'totalPrice' => 300.00,
            ],
        ]);

    // Assert that the item was added to the database
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 3,
    ]);
});

test('can update cart item quantity', function () {
    // Create a cart for the user
    $cart = Cart::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Add an item to the cart
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);    // Make request to update the cart item
    $response = $this->patchJson(route('cart.update', $cartItem->id), [
        'quantity' => 5,
    ]);

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Cart item updated',
            'cart_summary' => [
                'totalItems' => 5,
                'totalPrice' => 500.00,
            ],
        ]);

    // Assert that the item was updated in the database
    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 5,
    ]);
});

test('cannot update cart item that belongs to another user', function () {
    // Create another user with a cart
    $anotherUser = User::factory()->create();
    $anotherCart = Cart::factory()->create([
        'user_id' => $anotherUser->id,
    ]);

    // Add an item to the other user's cart
    $cartItem = CartItem::factory()->create([
        'cart_id' => $anotherCart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);    // Make request to update the cart item (should fail)
    $response = $this->patchJson(route('cart.update', $cartItem->id), [
        'quantity' => 5,
    ]);

    // Assert response is forbidden
    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthorized access to cart item',
        ]);

    // Assert that the item quantity was not updated
    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 2,
    ]);
});

test('can remove item from cart', function () {
    // Create a cart for the user
    $cart = Cart::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Add an item to the cart
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);    // Make request to remove the cart item
    $response = $this->deleteJson(route('cart.remove', ['cartItem' => $cartItem->id]));

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_summary' => [
                'totalItems' => 0,
                'totalPrice' => 0,
            ],
        ]);

    // Assert that the item was removed from the database
    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
});

test('cannot remove cart item that belongs to another user', function () {
    // Create another user with a cart
    $anotherUser = User::factory()->create();
    $anotherCart = Cart::factory()->create([
        'user_id' => $anotherUser->id,
    ]);

    // Add an item to the other user's cart
    $cartItem = CartItem::factory()->create([
        'cart_id' => $anotherCart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);    // Make request to remove the cart item (should fail)
    $response = $this->deleteJson(route('cart.remove', ['cartItem' => $cartItem->id]));

    // Assert response is forbidden
    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthorized access to cart item',
        ]);

    // Assert that the item was not removed from the database
    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
    ]);
});

test('can clear cart', function () {
    // Create a cart for the user
    $cart = Cart::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Add multiple items to the cart
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    $product2 = Product::factory()->create(['is_active' => true]);
    $variant2 = ProductVariant::factory()->create([
        'product_id' => $product2->id,
        'is_active' => true,
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product2->id,
        'product_variant_id' => $variant2->id,
        'quantity' => 3,
    ]);

    // Make request to clear the cart
    $response = $this->deleteJson(route('cart.clear'));

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Cart cleared',
            'cart_summary' => [
                'totalItems' => 0,
                'totalPrice' => 0,
            ],
        ]);

    // Assert that all items were removed from the database
    $this->assertDatabaseCount('cart_items', 0);
});

test('can get cart summary', function () {
    // Create a cart for the user
    $cart = Cart::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Add an item to the cart
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 4,
    ]);

    // Make request to get cart summary
    $response = $this->getJson(route('cart.summary'));

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'cart_summary' => [
                'totalItems' => 4,
                'totalPrice' => 400.00,
            ],
        ]);
});

test('validation fails when adding invalid product to cart', function () {
    // Attempt to add a non-existent product
    $response = $this->postJson(route('cart.add'), [
        'product_id' => 999999, // Non-existent product ID
        'quantity' => 3,
    ]);

    // Assert validation fails
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

test('validation fails when adding invalid quantity to cart', function () {
    // Attempt to add with invalid quantity
    $response = $this->postJson(route('cart.add'), [
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 0, // Invalid quantity
    ]);

    // Assert validation fails
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});
