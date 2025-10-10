<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

use function Pest\Laravel\get;

it('can generate facebook data feed csv', function () {
    // Create test data
    $brand = Brand::factory()->create([
        'name_en' => 'Test Brand',
        'is_active' => true,
    ]);

    $category = Category::factory()->create([
        'name_en' => 'Test Category',
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'name_en' => 'Test Product',
        'description_en' => 'This is a test product description',
        'price' => 100.00,
        'sale_price' => 80.00,
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'TEST-SKU-001',
        'quantity' => 10,
        'price' => 100.00,
        'sale_price' => 80.00,
        'color' => 'Blue',
        'size' => 'Medium',
        'is_active' => true,
        'images' => ['test-image.jpg'],
    ]);

    // Request the data feed
    $response = get(route('facebook.datafeed'));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename="datafeed.csv"');

    $content = $response->getContent();

    // Parse CSV
    $lines = explode("\n", trim($content));
    $headers = str_getcsv($lines[0]);
    $dataRow = str_getcsv($lines[1]);

    // Assert required Facebook fields are present in headers
    expect($headers)->toContain('id');
    expect($headers)->toContain('title');
    expect($headers)->toContain('description');
    expect($headers)->toContain('availability');
    expect($headers)->toContain('condition');
    expect($headers)->toContain('price');
    expect($headers)->toContain('link');
    expect($headers)->toContain('image_link');
    expect($headers)->toContain('brand');
    expect($headers)->toContain('quantity_to_sell_on_facebook');

    // Map headers to data
    $data = array_combine($headers, $dataRow);

    // Assert data values
    expect($data['id'])->toBe('TEST-SKU-001');
    expect($data['title'])->toBe('Test Product');
    expect($data['description'])->toContain('test product description');
    expect($data['availability'])->toBe('in stock');
    expect($data['condition'])->toBe('new');
    expect($data['price'])->toBe('80.00 EGP');
    expect($data['brand'])->toBe('Test Brand');
    expect($data['quantity_to_sell_on_facebook'])->toBe('10');
    expect($data['color'])->toBe('Blue');
    expect($data['size'])->toBe('Medium');
});

it('shows out of stock for products with zero quantity', function () {
    $brand = Brand::factory()->create(['is_active' => true]);
    $category = Category::factory()->create(['is_active' => true]);

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'OUT-OF-STOCK',
        'quantity' => 0,
        'is_active' => true,
    ]);

    $response = get(route('facebook.datafeed'));

    $content = $response->getContent();
    $lines = explode("\n", trim($content));
    $headers = str_getcsv($lines[0]);
    $dataRow = str_getcsv($lines[1]);
    $data = array_combine($headers, $dataRow);

    expect($data['availability'])->toBe('out of stock');
    expect($data['quantity_to_sell_on_facebook'])->toBe('0');
});

it('includes item group id for product variants', function () {
    $brand = Brand::factory()->create(['is_active' => true]);
    $category = Category::factory()->create(['is_active' => true]);

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    // Create multiple variants for the same product
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'VARIANT-1',
        'color' => 'Red',
        'size' => 'Small',
        'is_active' => true,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'VARIANT-2',
        'color' => 'Blue',
        'size' => 'Large',
        'is_active' => true,
    ]);

    $response = get(route('facebook.datafeed'));

    $content = $response->getContent();
    $lines = explode("\n", trim($content));
    $headers = str_getcsv($lines[0]);

    // Parse both variant rows
    $variant1Data = array_combine($headers, str_getcsv($lines[1]));
    $variant2Data = array_combine($headers, str_getcsv($lines[2]));

    // Both variants should have the same item_group_id
    expect($variant1Data['item_group_id'])->toBe("product_{$product->id}");
    expect($variant2Data['item_group_id'])->toBe("product_{$product->id}");

    // But different IDs
    expect($variant1Data['id'])->not->toBe($variant2Data['id']);
});

it('only includes active products and variants', function () {
    $brand = Brand::factory()->create(['is_active' => true]);
    $category = Category::factory()->create(['is_active' => true]);

    // Create inactive product
    $inactiveProduct = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => false,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $inactiveProduct->id,
        'sku' => 'INACTIVE-PRODUCT',
        'is_active' => true,
    ]);

    // Create active product with inactive variant
    $activeProduct = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $activeProduct->id,
        'sku' => 'INACTIVE-VARIANT',
        'is_active' => false,
    ]);

    // Create fully active product
    $fullyActiveProduct = Product::factory()->create([
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'is_active' => true,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $fullyActiveProduct->id,
        'sku' => 'ACTIVE',
        'is_active' => true,
    ]);

    $response = get(route('facebook.datafeed'));

    $content = $response->getContent();

    // Should not contain inactive product or inactive variant
    expect($content)->not->toContain('INACTIVE-PRODUCT');
    expect($content)->not->toContain('INACTIVE-VARIANT');

    // Should contain active product
    expect($content)->toContain('ACTIVE');
});
