<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Section;
use App\Models\User;

test('products with multiple color variants are loaded with all unique colors in sections', function () {
    $user = User::factory()->create();

    // Create a section
    $section = Section::factory()->create([
        'title_en' => 'Test Section',
        'section_type' => 'REAL',
        'active' => true,
    ]);

    // Create a product
    $product = Product::factory()->create([
        'name_en' => 'Test Product',
        'is_active' => true,
    ]);

    // Attach product to section
    $section->products()->attach($product->id);

    // Create variants with different colors
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Red',
        'size' => '25',
        'quantity' => 10,
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Red',
        'size' => '22',
        'quantity' => 5,
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Yellow',
        'size' => '25',
        'quantity' => 8,
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Yellow',
        'size' => '22',
        'quantity' => 3,
        'is_active' => true,
    ]);

    // Visit home page
    $response = $this->actingAs($user)->get('/');

    $response->assertSuccessful();

    // Check that section data is passed with variants
    $response->assertInertia(function ($page) use ($section, $product) {
        $sectionKey = "section_{$section->id}_page_data";

        // Ensure section data exists
        expect($page->toArray()['props'])->toHaveKey($sectionKey);

        $sectionProducts = $page->toArray()['props'][$sectionKey];

        // Product should be in the section
        expect($sectionProducts)->toHaveCount(1);

        // Product should have variants loaded
        expect($sectionProducts[0])->toHaveKey('variants');
        expect($sectionProducts[0]['variants'])->toHaveCount(4);

        // Verify all variants are present
        $colors = collect($sectionProducts[0]['variants'])->pluck('color')->unique()->values()->all();
        expect($colors)->toContain('Red');
        expect($colors)->toContain('Yellow');
    });
});

test('products with single color variants show only once', function () {
    $user = User::factory()->create();

    $section = Section::factory()->create([
        'title_en' => 'Test Section',
        'section_type' => 'REAL',
        'active' => true,
    ]);

    $product = Product::factory()->create([
        'name_en' => 'Single Color Product',
        'is_active' => true,
    ]);

    $section->products()->attach($product->id);

    // Create variants with same color
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Blue',
        'size' => '25',
        'quantity' => 10,
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Blue',
        'size' => '30',
        'quantity' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertSuccessful();

    $response->assertInertia(function ($page) use ($section) {
        $sectionKey = "section_{$section->id}_page_data";

        $sectionProducts = $page->toArray()['props'][$sectionKey];

        // Product should appear once even with multiple variants of same color
        expect($sectionProducts)->toHaveCount(1);

        // All variants should be passed to frontend for proper handling
        expect($sectionProducts[0]['variants'])->toHaveCount(2);
    });
});

