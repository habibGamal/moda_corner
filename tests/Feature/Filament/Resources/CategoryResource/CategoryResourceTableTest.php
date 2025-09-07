<?php

use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('has column', function (string $column) {
    livewire(ListCategories::class)
        ->assertTableColumnExists($column);
})->with([fn() => 'name_' . app()->getLocale(), 'image', 'is_active', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListCategories::class)
        ->assertCanRenderTableColumn($column);
})->with([fn() => 'name_' . app()->getLocale(), 'image', 'is_active', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = Category::factory(3)->create();

    livewire(ListCategories::class)
        ->sortTable($column)
        ->assertSuccessful()
        ->sortTable($column, 'desc')
        ->assertSuccessful();
})->with([fn() => 'name_' . app()->getLocale(), 'is_active', 'created_at', 'updated_at']);
