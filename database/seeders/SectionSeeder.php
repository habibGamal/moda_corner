<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a featured products section (VIRTUAL)
        $featuredSection = Section::create([
            'title_en' => 'Featured Products',
            'title_ar' => 'منتجات مميزة',
            'active' => true,
            'sort_order' => 1,
            'section_type' => SectionType::VIRTUAL,
        ]);

        // Create a sale products section (VIRTUAL)
        $saleSection = Section::create([
            'title_en' => 'Products On Sale',
            'title_ar' => 'منتجات في التخفيض',
            'active' => true,
            'sort_order' => 2,
            'section_type' => SectionType::VIRTUAL,
        ]);

        // Create some REAL sections with products
        $newProductsSection = Section::create([
            'title_en' => 'New Arrivals',
            'title_ar' => 'وصل حديثاً',
            'active' => true,
            'sort_order' => 3,
            'section_type' => SectionType::VIRTUAL,
        ]);

        // Create a best sellers section (VIRTUAL)
        $bestSellersSection = Section::create([
            'title_en' => 'Best Sellers',
            'title_ar' => 'الأكثر مبيعاً',
            'active' => true,
            'sort_order' => 4,
            'section_type' => SectionType::VIRTUAL,
        ]);

        // Another REAL section
        $recommendedSection = Section::create([
            'title_en' => 'Recommended For You',
            'title_ar' => 'موصى به لك',
            'active' => true,
            'sort_order' => 5,
            'section_type' => SectionType::VIRTUAL,
        ]);
    }
}
