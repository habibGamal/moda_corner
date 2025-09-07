<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo announcements
        Announcement::create([
            'title_en' => 'Free shipping on all orders over 750 EGP',
            'title_ar' => 'شحن مجاني على جميع الطلبات التي تزيد عن 750 جنيه',
            'is_active' => true,
            'display_order' => 1,
        ]);

        Announcement::create([
            'title_en' => 'Summer sale: Up to 50% off',
            'title_ar' => 'تخفيضات الصيف: خصم يصل إلى 50%',
            'is_active' => true,
            'display_order' => 2,
        ]);

        Announcement::create([
            'title_en' => 'New arrivals: Check out our latest products',
            'title_ar' => 'منتجات جديدة: تحقق من أحدث منتجاتنا',
            'is_active' => true,
            'display_order' => 3,
        ]);
    }
}
