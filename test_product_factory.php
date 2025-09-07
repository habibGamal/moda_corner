<?php

require_once 'vendor/autoload.php';

// Test script to verify ProductFactory variant creation
use App\Models\Product;
use App\Models\ProductVariant;

// Test 1: Create product with variants (default behavior)
echo "Test 1: Creating product with variants (default)\n";
$productWithVariants = Product::factory()->create();
$variantCount = ProductVariant::where('product_id', $productWithVariants->id)->count();
echo "Product ID: {$productWithVariants->id}, Variants created: {$variantCount}\n\n";

// Test 2: Create product without variants
echo "Test 2: Creating product without variants\n";
$productWithoutVariants = Product::factory()->withoutVariants()->create();
$variantCount = ProductVariant::where('product_id', $productWithoutVariants->id)->count();
echo "Product ID: {$productWithoutVariants->id}, Variants created: {$variantCount}\n\n";

// Test 3: Create multiple products without variants
echo "Test 3: Creating multiple products without variants\n";
$products = Product::factory()->withoutVariants()->count(3)->create();
foreach ($products as $product) {
    $variantCount = ProductVariant::where('product_id', $product->id)->count();
    echo "Product ID: {$product->id}, Variants created: {$variantCount}\n";
}

echo "\nTests completed!\n";
