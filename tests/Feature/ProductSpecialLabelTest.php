<?php

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

test('product can have a special label', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'special_label' => 'Limited Edition',
    ]);

    expect($product->special_label)->toBe('Limited Edition');
});

test('product can be created without a special label', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'special_label' => null,
    ]);

    expect($product->special_label)->toBeNull();
});

test('product special label is included in fillable attributes', function () {
    expect(Product::make()->getFillable())->toContain('special_label');
});

