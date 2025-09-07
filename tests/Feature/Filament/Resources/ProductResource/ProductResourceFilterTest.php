<?php

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can filter by category', function () {
    $category1 = Category::factory()->create(['name_en' => 'Electronics']);
    $category2 = Category::factory()->create(['name_en' => 'Clothing']);

    $product1 = Product::factory()->create(['category_id' => $category1->id]);
    $product2 = Product::factory()->create(['category_id' => $category2->id]);

    livewire(ListProducts::class)
        ->filterTable('category_id', $category1->id)
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('can filter by brand', function () {
    $brand1 = Brand::factory()->create(['name_en' => 'Apple']);
    $brand2 = Brand::factory()->create(['name_en' => 'Samsung']);

    $product1 = Product::factory()->create(['brand_id' => $brand1->id]);
    $product2 = Product::factory()->create(['brand_id' => $brand2->id]);

    livewire(ListProducts::class)
        ->filterTable('brand_id', $brand1->id)
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('can filter by active status', function () {
    $activeProduct = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->create(['is_active' => false]);

    livewire(ListProducts::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeProduct])
        ->assertCanNotSeeTableRecords([$inactiveProduct]);
});

it('can filter by featured status', function () {
    $featuredProduct = Product::factory()->create(['is_featured' => true]);
    $nonFeaturedProduct = Product::factory()->create(['is_featured' => false]);

    livewire(ListProducts::class)
        ->filterTable('is_featured', true)
        ->assertCanSeeTableRecords([$featuredProduct])
        ->assertCanNotSeeTableRecords([$nonFeaturedProduct]);
});
