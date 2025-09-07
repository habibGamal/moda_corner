<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories and brands
        $categories = Category::all();
        $brands = Brand::all();

        // Create 50 products with random categories and brands
        Product::factory()
            ->count(50)
            ->create([
                'category_id' => fn() => $categories->random()->id,
                'brand_id' => fn() => $brands->random()->id,
            ]);

        // Create 10 featured products
        Product::factory()
            ->count(10)
            ->create([
                'category_id' => fn() => $categories->random()->id,
                'brand_id' => fn() => $brands->random()->id,
                'is_featured' => true,
            ]);
    }
}
