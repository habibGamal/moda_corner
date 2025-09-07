<?php

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('has column', function (string $column) {
    livewire(ListProducts::class)
        ->assertTableColumnExists($column);
})->with([
    fn() => 'name_' . app()->getLocale(),
    'featured_image',
    'price',
    'sale_price',
    fn() => 'category.name_' . app()->getLocale(),
    fn() => 'brand.name_' . app()->getLocale(),
    'is_active',
    'is_featured',
    'total_quantity',
    'created_at',
    'updated_at'
]);

it('can render column', function (string $column) {
    $product = Product::factory()->create();

    livewire(ListProducts::class)
        ->assertCanRenderTableColumn($column);
})->with([
    fn() => 'name_' . app()->getLocale(),
    'featured_image',
    'price',
    'sale_price',
    fn() => 'category.name_' . app()->getLocale(),
    fn() => 'brand.name_' . app()->getLocale(),
    'is_active',
    'is_featured',
    'total_quantity',
    'created_at',
    'updated_at'
]);

it('can sort column', function (string $column) {
    $records = Product::factory(3)->create();

    livewire(ListProducts::class)
        ->sortTable($column)
        ->assertSuccessful()
        ->sortTable($column, 'desc')
        ->assertSuccessful();
})->with([
    fn() => 'name_' . app()->getLocale(),
    'price',
    'sale_price',
    'is_active',
    'is_featured',
    'created_at',
    'updated_at'
]);
