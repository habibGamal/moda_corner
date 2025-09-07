<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Models\Category;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('can validate required', function (string $column) {
    livewire(CreateCategory::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name_en', 'name_ar', 'slug']);

it('can validate unique', function (string $column) {
    $record = Category::factory()->create();

    livewire(CreateCategory::class)
        ->fillForm([
            'name_en' => 'Test Category EN',
            'name_ar' => 'فئة تجريبية',
            'slug' => $record->slug, // Use existing slug to trigger unique validation
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['unique']]);
})->with(['slug']);
