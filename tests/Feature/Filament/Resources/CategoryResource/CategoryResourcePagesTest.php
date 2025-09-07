<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('can render the index page', function () {
    livewire(ListCategories::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateCategory::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});
