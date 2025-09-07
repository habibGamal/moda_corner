<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can create a record', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $record = Product::factory()->make();

    // Ensure sale_price is less than or equal to price to avoid validation errors
    $salePrice = $record->sale_price && $record->sale_price > $record->price
        ? $record->price - 10
        : $record->sale_price;

    livewire(CreateProduct::class)
        ->fillForm([
            'name_en' => $record->name_en,
            'name_ar' => $record->name_ar,
            'slug' => $record->slug,
            'description_en' => $record->description_en,
            'description_ar' => $record->description_ar,
            'price' => $record->price,
            'sale_price' => $salePrice,
            'cost_price' => $record->cost_price,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => $record->is_active,
            'is_featured' => $record->is_featured,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name_en' => $record->name_en,
        'name_ar' => $record->name_ar,
        'slug' => $record->slug,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'is_active' => $record->is_active,
        'is_featured' => $record->is_featured,
    ]);
});

it('can update a record', function () {
    $record = Product::factory()->create();
    $newRecord = Product::factory()->make();
    $newCategory = Category::factory()->create();
    $newBrand = Brand::factory()->create();

    // Ensure sale_price is less than or equal to price to avoid validation errors
    $salePrice = $newRecord->sale_price && $newRecord->sale_price > $newRecord->price
        ? $newRecord->price - 10
        : $newRecord->sale_price;

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name_en' => $newRecord->name_en,
            'name_ar' => $newRecord->name_ar,
            'slug' => $newRecord->slug,
            'description_en' => $newRecord->description_en,
            'description_ar' => $newRecord->description_ar,
            'price' => $newRecord->price,
            'sale_price' => $salePrice,
            'cost_price' => $newRecord->cost_price,
            'category_id' => $newCategory->id,
            'brand_id' => $newBrand->id,
            'is_active' => $newRecord->is_active,
            'is_featured' => $newRecord->is_featured,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'id' => $record->id,
        'name_en' => $newRecord->name_en,
        'name_ar' => $newRecord->name_ar,
        'slug' => $newRecord->slug,
        'category_id' => $newCategory->id,
        'brand_id' => $newBrand->id,
        'is_active' => $newRecord->is_active,
        'is_featured' => $newRecord->is_featured,
    ]);
});

it('can delete a record', function () {
    $record = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Product::factory(5)->create();

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can create product with pricing constraints', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي',
            'slug' => 'test-product-pricing',
            'price' => 200,
            'sale_price' => 150, // Valid: less than price
            'cost_price' => 100,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name_en' => 'Test Product',
        'slug' => 'test-product-pricing',
        'price' => 200,
        'sale_price' => 150,
        'cost_price' => 100,
    ]);
});
