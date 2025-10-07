<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        // Available product variant images
        $availableImages = [
            'product-variants/01K4JQR3RR1TTRNMPQ4W29NSYY.webp',
            'product-variants/01K4JRKPQVRA22Z4K1SD1FT4QW.webp',
            'product-variants/01K4JRKQ7EY24M4FXFW2MMQN08.webp',
            'product-variants/01K4JRPS5PANZKK9NRB7TYBS0K.webp',
            'product-variants/01K4JRPSBY95K73T11DXK36ARS.webp',
        ];

        // Sample product data
        $productData = [
            [
                'name_en' => 'Classic Cotton T-Shirt',
                'name_ar' => 'قميص قطني كلاسيكي',
                'description_en' => 'A comfortable and stylish cotton t-shirt perfect for everyday wear. Made from 100% premium cotton.',
                'description_ar' => 'قميص قطني مريح وأنيق مثالي للاستخدام اليومي. مصنوع من القطن الفاخر 100%.',
                'price' => 49.99,
                'cost_price' => 25.00,
                'color' => 'Navy Blue',
                'size' => 'M',
            ],
            [
                'name_en' => 'Denim Jacket',
                'name_ar' => 'جاكيت جينز',
                'description_en' => 'A trendy denim jacket that pairs well with any outfit. Durable and fashionable.',
                'description_ar' => 'جاكيت جينز عصري يتماشى مع أي ملبس. متين وأنيق.',
                'price' => 89.99,
                'sale_price' => 69.99,
                'cost_price' => 45.00,
                'color' => 'Dark Blue',
                'size' => 'L',
            ],
            [
                'name_en' => 'Casual Sneakers',
                'name_ar' => 'حذاء رياضي كاجوال',
                'description_en' => 'Comfortable casual sneakers suitable for daily activities. Lightweight and breathable.',
                'description_ar' => 'حذاء رياضي كاجوال مريح مناسب للأنشطة اليومية. خفيف الوزن ومسامي.',
                'price' => 79.99,
                'cost_price' => 40.00,
                'color' => 'White',
                'size' => '42',
            ],
            [
                'name_en' => 'Elegant Dress',
                'name_ar' => 'فستان أنيق',
                'description_en' => 'An elegant dress perfect for special occasions. Made from high-quality fabric.',
                'description_ar' => 'فستان أنيق مثالي للمناسبات الخاصة. مصنوع من قماش عالي الجودة.',
                'price' => 129.99,
                'sale_price' => 99.99,
                'cost_price' => 65.00,
                'color' => 'Black',
                'size' => 'S',
            ],
            [
                'name_en' => 'Sports Hoodie',
                'name_ar' => 'هودي رياضي',
                'description_en' => 'A cozy sports hoodie perfect for workouts or casual wear. Soft and warm.',
                'description_ar' => 'هودي رياضي مريح مثالي للتمارين أو الاستخدام العادي. ناعم ودافئ.',
                'price' => 59.99,
                'cost_price' => 30.00,
                'color' => 'Gray',
                'size' => 'XL',
            ],
            [
                'name_en' => 'Leather Wallet',
                'name_ar' => 'محفظة جلدية',
                'description_en' => 'A premium leather wallet with multiple compartments. Stylish and functional.',
                'description_ar' => 'محفظة جلدية فاخرة مع عدة أقسام. أنيقة وعملية.',
                'price' => 39.99,
                'cost_price' => 20.00,
                'color' => 'Brown',
                'size' => null,
            ],
            [
                'name_en' => 'Wireless Headphones',
                'name_ar' => 'سماعات لاسلكية',
                'description_en' => 'High-quality wireless headphones with noise cancellation. Perfect for music lovers.',
                'description_ar' => 'سماعات لاسلكية عالية الجودة مع إلغاء الضوضاء. مثالية لعشاق الموسيقى.',
                'price' => 149.99,
                'sale_price' => 119.99,
                'cost_price' => 75.00,
                'color' => 'Black',
                'capacity' => '32GB',
            ],
            [
                'name_en' => 'Summer Shorts',
                'name_ar' => 'شورت صيفي',
                'description_en' => 'Light and comfortable summer shorts. Perfect for hot weather.',
                'description_ar' => 'شورت صيفي خفيف ومريح. مثالي للطقس الحار.',
                'price' => 34.99,
                'cost_price' => 18.00,
                'color' => 'Khaki',
                'size' => 'M',
            ],
            [
                'name_en' => 'Smart Watch',
                'name_ar' => 'ساعة ذكية',
                'description_en' => 'Advanced smart watch with health monitoring features. Stay connected and healthy.',
                'description_ar' => 'ساعة ذكية متقدمة مع ميزات مراقبة الصحة. ابق متصلاً وبصحة جيدة.',
                'price' => 199.99,
                'cost_price' => 100.00,
                'color' => 'Silver',
                'capacity' => '64GB',
            ],
            [
                'name_en' => 'Backpack',
                'name_ar' => 'حقيبة ظهر',
                'description_en' => 'Spacious and durable backpack for daily use. Multiple compartments for organization.',
                'description_ar' => 'حقيبة ظهر واسعة ومتينة للاستخدام اليومي. أقسام متعددة للتنظيم.',
                'price' => 69.99,
                'sale_price' => 54.99,
                'cost_price' => 35.00,
                'color' => 'Navy Blue',
                'size' => null,
            ],
        ];

        // Create 10 products with one variant each
        for ($i = 0; $i < 10; $i++) {
            $data = $productData[$i];
            $imageIndex = $i % count($availableImages);

            // Create the product
            $product = Product::create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => Str::slug($data['name_en']),
                'description_en' => $data['description_en'],
                'description_ar' => $data['description_ar'],
                'price' => $data['price'],
                'sale_price' => $data['sale_price'] ?? null,
                'cost_price' => $data['cost_price'],
                'category_id' => $categories->random()->id,
                'brand_id' => $brands->random()->id,
                'is_active' => true,
                'is_featured' => $i < 3, // Make first 3 products featured
            ]);

            // Create one variant for each product
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-'.strtoupper(Str::random(8)).'-'.$product->id,
                'images' => [$availableImages[$imageIndex]], // Use one image per variant
                'quantity' => rand(5, 50),
                'price' => null, // Use product price
                'sale_price' => null, // Use product sale price
                'color' => $data['color'] ?? null,
                'size' => $data['size'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'additional_attributes' => null,
                'is_default' => true,
                'is_active' => true,
            ]);
        }
    }
}
