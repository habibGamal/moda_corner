<?php

namespace Database\Seeders;

use App\Models\DirectPromotion;
use Illuminate\Database\Seeder;

class DirectPromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample direct promotions

        // Global discount promotion
        DirectPromotion::create([
            'name_ar' => 'خصم 15% على جميع المنتجات',
            'name_en' => '15% Off All Products',
            'description_ar' => 'احصل على خصم 15% على جميع المنتجات في المتجر',
            'description_en' => 'Get 15% discount on all products in the store',
            'type' => 'price_discount',
            'discount_percentage' => 15.00,
            'apply_to' => 'all_products',
            'is_active' => false, // Start inactive
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        // Free shipping promotion
        DirectPromotion::create([
            'name_ar' => 'شحن مجاني للطلبات أكثر من 1500 جنيه',
            'name_en' => 'Free Shipping on Orders Over 1500 EGP',
            'description_ar' => 'احصل على شحن مجاني عند الطلب بقيمة 1500 جنيه أو أكثر',
            'description_en' => 'Get free shipping when you order 1500 EGP or more',
            'type' => 'free_shipping',
            'minimum_order_amount' => 1500.00,
            'is_active' => true,
            'starts_at' => now(),
        ]);

        // Category-specific promotion (inactive by default)
        DirectPromotion::create([
            'name_ar' => 'خصم 20% على فئة معينة',
            'name_en' => '20% Off Specific Category',
            'description_ar' => 'خصم خاص على فئة محددة من المنتجات',
            'description_en' => 'Special discount on a specific category of products',
            'type' => 'price_discount',
            'discount_percentage' => 20.00,
            'apply_to' => 'category',
            'category_id' => 1, // Will need to be updated with actual category ID
            'is_active' => false,
            'starts_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);
    }
}
