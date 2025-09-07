<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List of placeholder category images from Unsplash - suitable for main categories
        $categoryImages = [
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=400',  // Fashion
            'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?q=80&w=400',  // Electronics
            'https://images.unsplash.com/photo-1487700160041-babef9c3cb55?q=80&w=400',  // Home & Living
            'https://images.unsplash.com/photo-1470309864661-68328b2cd0a5?q=80&w=400',  // Sports
            'https://images.unsplash.com/photo-1513094735237-8f2714d57c13?q=80&w=400',  // Beauty
        ];

        // List of placeholder category images for subcategories
        $subCategoryImages = [
            'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=400',  // Clothing
            'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=400',  // Audio
            'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?q=80&w=400',  // Furniture
            'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=400',  // Sports Equipment
            'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=400',  // Makeup
        ];

        // Create parent categories
        $parentCategories = Category::factory()
            ->count(5)
            ->create()
            ->each(function ($category, $index) use ($categoryImages) {
                // Assign a main category image
                $category->update([
                    'image' => $categoryImages[$index % count($categoryImages)],
                ]);
            });

        // Create child categories for each parent
        foreach ($parentCategories as $index => $parentCategory) {
            Category::factory()
                ->count(3)
                ->create([
                    'parent_id' => $parentCategory->id,
                ])
                ->each(function ($category, $subIndex) use ($subCategoryImages, $index) {
                    // Assign a subcategory image
                    $imageIndex = ($index * 3 + $subIndex) % count($subCategoryImages);
                    $category->update([
                        'image' => $subCategoryImages[$imageIndex],
                    ]);
                });
        }
    }
}
