<?php

namespace Tests\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryTestUtility
{
    /**
     * Clean up category-related tables for testing
     */
    public static function cleanupTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Storage::fake('public');
    }

    /**
     * Clean up all category-related tables including additional ones
     */
    public static function cleanupAllTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::table('products')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Storage::fake('public');
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
        Storage::fake('public');
    }
}
