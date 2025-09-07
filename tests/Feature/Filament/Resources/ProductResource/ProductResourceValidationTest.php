<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can validate required fields', function (string $column) {
    livewire(CreateProduct::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name_en', 'name_ar', 'slug', 'price', 'category_id', 'brand_id']);

it('can validate unique slug', function () {
    $record = Product::factory()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'name_en' => 'Test Product EN',
            'name_ar' => 'منتج تجريبي',
            'slug' => $record->slug, // Use existing slug to trigger unique validation
            'price' => 100,
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors(['slug' => ['unique']]);
});

it('can validate numeric fields', function (string $field) {
    livewire(CreateProduct::class)
        ->fillForm([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي',
            'slug' => 'test-product',
            $field => 'not-a-number',
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$field => ['numeric']]);
})->with(['price', 'sale_price', 'cost_price']);

it('can validate sale price is less than or equal to price', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي',
            'slug' => 'test-product',
            'price' => 100,
            'sale_price' => 150, // Higher than price
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors(['sale_price' => ['lte']]);
});
