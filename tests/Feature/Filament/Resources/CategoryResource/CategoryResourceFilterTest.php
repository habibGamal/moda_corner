<?php

use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('can filter by parent category', function () {
    $parentCategory = Category::factory()->create(['name_en' => 'Parent Category']);
    $childCategory = Category::factory()->create([
        'parent_id' => $parentCategory->id,
        'name_en' => 'Child Category'
    ]);
    $anotherCategory = Category::factory()->create(['name_en' => 'Another Category']);

    livewire(ListCategories::class)
        ->filterTable('parent_id', $parentCategory->id)
        ->assertCanSeeTableRecords([$childCategory])
        ->assertCanNotSeeTableRecords([$parentCategory, $anotherCategory]);
});

it('can filter by active status', function () {
    $activeCategory = Category::factory()->create([
        'is_active' => true,
        'name_en' => 'Active Category'
    ]);
    $inactiveCategory = Category::factory()->create([
        'is_active' => false,
        'name_en' => 'Inactive Category'
    ]);

    livewire(ListCategories::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeCategory])
        ->assertCanNotSeeTableRecords([$inactiveCategory]);
});
