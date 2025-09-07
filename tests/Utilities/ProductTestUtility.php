<?php

namespace Tests\Utilities;

use Illuminate\Support\Facades\DB;

class ProductTestUtility
{
    /**
     * Clean up product-related tables for testing
     */
    public static function cleanupTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('categories')->truncate();
        DB::table('brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Clean up all product-related tables including additional ones
     */
    public static function cleanupAllTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('categories')->truncate();
        DB::table('brands')->truncate();
        DB::table('wishlist_items')->truncate();
        DB::table('section_product')->truncate();
        DB::table('sections')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Clean up specific tables
     */
    public static function cleanupSpecificTables(array $tables): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Clean up only basic product tables (without relationships)
     */
    public static function cleanupBasicTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_variants')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Clean up category-related tables only
     */
    public static function cleanupCategoryTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Clean up brand-related tables only
     */
    public static function cleanupBrandTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Setup test environment with sample data
     */
    public static function setupWithSampleData(): array
    {
        self::cleanupTables();

        return [
            'category' => \App\Models\Category::factory()->create(),
            'brand' => \App\Models\Brand::factory()->create(),
            'products' => \App\Models\Product::factory(3)->create(),
        ];
    }
}
