<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Tests\Utilities\CategoryTestUtility;

use function Pest\Livewire\livewire;

beforeEach(function () {
    CategoryTestUtility::cleanupTables();
});

it('can create a record', function () {
    $record = Category::factory()->make();

    livewire(CreateCategory::class)
        ->fillForm([
            'name_en' => $record->name_en,
            'name_ar' => $record->name_ar,
            'slug' => $record->slug,
            'is_active' => $record->is_active,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name_en' => $record->name_en,
        'name_ar' => $record->name_ar,
        'slug' => $record->slug,
        'is_active' => $record->is_active,
    ]);
});

it('can create a record with image', function () {
    $record = Category::factory()->make();

    livewire(CreateCategory::class)
        ->fillForm([
            'name_en' => $record->name_en,
            'name_ar' => $record->name_ar,
            'slug' => $record->slug,
            'image' => UploadedFile::fake()->image('category.jpg'),
            'is_active' => $record->is_active,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $createdRecord = Category::where('slug', $record->slug)->first();
    expect($createdRecord)->not()->toBeNull();
    expect($createdRecord->image)->not()->toBeNull();
});

it('can update a record', function () {
    $record = Category::factory()->create();
    $newRecord = Category::factory()->make();

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name_en' => $newRecord->name_en,
            'name_ar' => $newRecord->name_ar,
            'slug' => $newRecord->slug,
            'is_active' => $newRecord->is_active,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name_en' => $newRecord->name_en,
        'name_ar' => $newRecord->name_ar,
        'slug' => $newRecord->slug,
        'is_active' => $newRecord->is_active,
    ]);
});

it('can update a record with image', function () {
    $record = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'image' => UploadedFile::fake()->image('updated_category.jpg'),
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    // Check that the record was updated
    $record->refresh();
    expect($record->image)->not()->toBeNull();
});

it('can create a record with parent category', function () {
    $parentCategory = Category::factory()->create();
    $record = Category::factory()->make();

    livewire(CreateCategory::class)
        ->fillForm([
            'name_en' => $record->name_en,
            'name_ar' => $record->name_ar,
            'slug' => $record->slug,
            'parent_id' => $parentCategory->id,
            'image' => UploadedFile::fake()->image('category.jpg'),
            'is_active' => $record->is_active,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name_en' => $record->name_en,
        'name_ar' => $record->name_ar,
        'slug' => $record->slug,
        'parent_id' => $parentCategory->id,
        'is_active' => $record->is_active,
    ]);
});
