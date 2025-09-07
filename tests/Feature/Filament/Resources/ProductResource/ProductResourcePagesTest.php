<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can render the index page', function () {
    livewire(ListProducts::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateProduct::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('can render the view page', function () {
    $record = Product::factory()->create();

    livewire(ViewProduct::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});
