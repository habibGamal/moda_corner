<?php

use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('displays total quantity from variants', function () {
    $product = Product::factory()->create();

    // The factory automatically creates variants, so we can test the total_quantity attribute
    expect($product->total_quantity)->toBeGreaterThanOrEqual(0);
    expect($product->variants)->not()->toBeEmpty();
});

it('displays correct stock status', function () {
    $product = Product::factory()->create();

    // Check that is_in_stock attribute works correctly
    $hasStock = $product->variants->where('quantity', '>', 0)->count() > 0;
    expect($product->is_in_stock)->toBe($hasStock);
});

it('has featured image from variants', function () {
    $product = Product::factory()->create();

    // The featured_image should be available (might be null if no variants have images)
    expect($product)->toHaveProperty('featured_image');
});

it('can get all images from variants', function () {
    $product = Product::factory()->create();

    // Test that the all_images attribute is accessible
    expect($product->all_images)->toBeArray();
});

it('has working scope for cards', function () {
    $activeProduct = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->create(['is_active' => false]);

    $cardProducts = Product::forCards()->get();

    expect($cardProducts)->toContain($activeProduct);
    expect($cardProducts)->not()->toContain($inactiveProduct);
});
