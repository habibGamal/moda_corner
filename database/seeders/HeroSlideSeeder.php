<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo hero slides
        HeroSlide::create([
            'title_en' => 'New Collection',
            'title_ar' => 'مجموعة جديدة',
            'description_en' => 'Discover our latest products with premium quality and exclusive deals.',
            'description_ar' => 'اكتشف أحدث منتجاتنا ذات الجودة العالية والعروض الحصرية.',
            'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1200',
            'cta_link' => '/products/new-arrivals',
            'is_active' => true,
            'display_order' => 1,
        ]);

        HeroSlide::create([
            'title_en' => 'Summer Sale',
            'title_ar' => 'تخفيضات الصيف',
            'description_en' => 'Up to 50% off on selected items. Limited time offer.',
            'description_ar' => 'خصم يصل إلى 50٪ على منتجات مختارة. عرض لفترة محدودة.',
            'image' => 'https://images.unsplash.com/photo-1574634534894-89d7576c8259?q=80&w=1200',
            'cta_link' => '/products/on-sale',
            'is_active' => true,
            'display_order' => 2,
        ]);

        HeroSlide::create([
            'title_en' => 'Exclusive Deals',
            'title_ar' => 'عروض حصرية',
            'description_en' => 'Shop our exclusive deals with free shipping on all orders.',
            'description_ar' => 'تسوق من عروضنا الحصرية مع شحن مجاني على جميع الطلبات.',
            'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=1200',
            'cta_link' => '/products/deals',
            'is_active' => true,
            'display_order' => 3,
        ]);
    }
}
