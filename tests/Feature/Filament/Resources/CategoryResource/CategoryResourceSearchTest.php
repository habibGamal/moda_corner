<?php

use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('can search column', function () {
    // Get the current app locale to match what the table is searching
    app()->setLocale('en');

    $record = Category::factory()->create([
        'name_en' => 'Unique Test Category EN',
        'name_ar' => 'فئة تجريبية فريدة',
    ]);

    // Create some other records with different names
    Category::factory(3)->create([
        'name_en' => 'Other Category',
        'name_ar' => 'فئة أخرى',
    ]);

    // Search for the English name since app locale is 'en' by default
    livewire(ListCategories::class)
        ->searchTable('Unique Test Category EN')
        ->assertCanSeeTableRecords([$record]);
});

it('can search column in Arabic locale', function () {
    // Set the app locale to Arabic
    app()->setLocale('ar');

    $record = Category::factory()->create([
        'name_en' => 'Unique Test Category EN',
        'name_ar' => 'فئة تجريبية فريدة',
    ]);

    // Create some other records with different names
    Category::factory(3)->create([
        'name_en' => 'Other Category',
        'name_ar' => 'فئة أخرى',
    ]);

    // Search for the Arabic name since app locale is now 'ar'
    livewire(ListCategories::class)
        ->searchTable('فئة تجريبية فريدة')
        ->assertCanSeeTableRecords([$record]);

    // Reset locale back to default
    app()->setLocale('en');
});
