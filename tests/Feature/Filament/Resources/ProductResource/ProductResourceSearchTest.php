<?php

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Tests\Utilities\ProductTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    ProductTestUtility::cleanupTables();
});

it('can search column', function () {
    // Get the current app locale to match what the table is searching
    app()->setLocale('en');

    $record = Product::factory()->create([
        'name_en' => 'Unique Test Product EN',
        'name_ar' => 'منتج تجريبي فريد',
    ]);

    // Create some other records with different names
    Product::factory(3)->create([
        'name_en' => 'Other Product',
        'name_ar' => 'منتج آخر',
    ]);

    // Search for the English name since app locale is 'en' by default
    livewire(ListProducts::class)
        ->searchTable('Unique Test Product EN')
        ->assertCanSeeTableRecords([$record]);
});

it('can search column in Arabic locale', function () {
    // Set the app locale to Arabic
    app()->setLocale('ar');

    $record = Product::factory()->create([
        'name_en' => 'Unique Test Product EN',
        'name_ar' => 'منتج تجريبي فريد',
    ]);

    // Create some other records with different names
    Product::factory(3)->create([
        'name_en' => 'Other Product',
        'name_ar' => 'منتج آخر',
    ]);

    // Search for the Arabic name since app locale is now 'ar'
    livewire(ListProducts::class)
        ->searchTable('منتج تجريبي فريد')
        ->assertCanSeeTableRecords([$record]);

    // Reset locale back to default
    app()->setLocale('en');
});

it('can search in globally searchable attributes', function () {
    $product = Product::factory()->create([
        'name_en' => 'iPhone 15 Pro',
        'name_ar' => 'آيفون 15 برو',
        'description_en' => 'Latest iPhone with titanium design',
        'description_ar' => 'أحدث آيفون بتصميم التيتانيوم',
    ]);

    // Test search by English name
    livewire(ListProducts::class)
        ->searchTable('iPhone 15 Pro')
        ->assertCanSeeTableRecords([$product]);

    // Test search by description
    livewire(ListProducts::class)
        ->searchTable('titanium design')
        ->assertCanSeeTableRecords([$product]);
});
