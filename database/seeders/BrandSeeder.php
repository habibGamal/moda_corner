<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent brands
        // List of placeholder brand images from Unsplash
        $brandImages = [
            'https://images.unsplash.com/photo-1523381294911-8d3cead13475?q=80&w=400',  // Nike-like
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=400',     // Shoe
            'https://images.unsplash.com/photo-1581655353564-df123a1eb820?q=80&w=400',  // Fashion
            'https://images.unsplash.com/photo-1539874754764-5a96559165b0?q=80&w=400',  // Tech
            'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?q=80&w=400',  // Luxury
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?q=80&w=400',  // Car
            'https://images.unsplash.com/photo-1556742212-5b321f3c261b?q=80&w=400',     // Watch
            'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=400',  // Footwear
            'https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=400',  // Headphone
            'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?q=80&w=400',  // Jewelry
        ];

        Brand::factory()
            ->count(8)
            ->create()
            ->each(function ($brand, $index) use ($brandImages) {
                // Assign a random image from the list
                $brand->update([
                    'image' => $brandImages[$index % count($brandImages)],
                ]);
            });
    }
}
