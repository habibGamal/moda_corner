<?php

namespace Tests\Utilities;

use Illuminate\Support\Facades\DB;

class ResourceTestUtility
{
    /**
     * Clean up all tables with foreign key checks disabled
     */
    public static function cleanupTables(array $tables): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Common product-related tables
     */
    public static function getProductTables(): array
    {
        return [
            'products',
            'product_variants',
            'categories',
            'brands',
        ];
    }

    /**
     * Extended product-related tables including relationships
     */
    public static function getExtendedProductTables(): array
    {
        return [
            'products',
            'product_variants',
            'categories',
            'brands',
            'wishlist_items',
            'section_product',
            'sections',
        ];
    }

    /**
     * Category-specific tables
     */
    public static function getCategoryTables(): array
    {
        return [
            'categories',
        ];
    }

    /**
     * Brand-specific tables
     */
    public static function getBrandTables(): array
    {
        return [
            'brands',
        ];
    }

    /**
     * User-related tables
     */
    public static function getUserTables(): array
    {
        return [
            'users',
            'wishlist_items',
        ];
    }

    /**
     * Order-related tables
     */
    public static function getOrderTables(): array
    {
        return [
            'orders',
            'order_items',
            'payments',
        ];
    }

    /**
     * Clean up product-related tables
     */
    public static function cleanupProductTables(): void
    {
        self::cleanupTables(self::getProductTables());
    }

    /**
     * Clean up extended product-related tables
     */
    public static function cleanupExtendedProductTables(): void
    {
        self::cleanupTables(self::getExtendedProductTables());
    }
}
