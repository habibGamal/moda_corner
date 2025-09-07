<?php

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can bulk activate products', function () {
    $products = Product::factory(3)->create(['is_active' => false]);

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('activate')
        ->callTableBulkAction('activate', $products);

    foreach ($products as $product) {
        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'is_active' => true,
        ]);
    }
});

it('can bulk deactivate products', function () {
    $products = Product::factory(3)->create(['is_active' => true]);

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('deactivate')
        ->callTableBulkAction('deactivate', $products);

    foreach ($products as $product) {
        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }
});

it('can bulk feature products', function () {
    $products = Product::factory(3)->create(['is_featured' => false]);

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('feature')
        ->callTableBulkAction('feature', $products);

    foreach ($products as $product) {
        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'is_featured' => true,
        ]);
    }
});

it('can bulk unfeature products', function () {
    $products = Product::factory(3)->create(['is_featured' => true]);

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('unfeature')
        ->callTableBulkAction('unfeature', $products);

    foreach ($products as $product) {
        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'is_featured' => false,
        ]);
    }
});
