<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_icon',
                'group' => 'general',
                'type' => 'image',
                'value' => null,
                'label_en' => 'Site Icon',
                'label_ar' => 'أيقونة الموقع',
                'description_en' => 'Upload site favicon icon',
                'description_ar' => 'رفع أيقونة الموقع المفضلة',
                'is_required' => false,
                'display_order' => 1,
            ],
            [
                'key' => 'site_title',
                'group' => 'general',
                'type' => 'text',
                'value' => 'Vilain',
                'label_en' => 'Site Title',
                'label_ar' => 'عنوان الموقع',
                'description_en' => 'The main title of your website',
                'description_ar' => 'العنوان الرئيسي لموقعك الإلكتروني',
                'is_required' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'site_description',
                'group' => 'general',
                'type' => 'text',
                'value' => 'Vilain is a modern fashion store offering curated apparel and accessories.',
                'label_en' => 'Site Description',
                'label_ar' => 'وصف الموقع',
                'description_en' => 'A short description of your website for SEO and meta tags',
                'description_ar' => 'وصف قصير لموقعك لاستخدامه في محركات البحث والوسوم',
                'is_required' => true,
                'display_order' => 2,
            ],
            [
                'key' => 'site_logo',
                'group' => 'general',
                'type' => 'image',
                'value' => null,
                'label_en' => 'Site Logo',
                'label_ar' => 'شعار الموقع',
                'description_en' => 'Upload your website logo',
                'description_ar' => 'رفع شعار موقعك الإلكتروني',
                'is_required' => false,
                'display_order' => 3,
            ],
            [
                'key' => 'site_logo_dark',
                'group' => 'general',
                'type' => 'image',
                'value' => null,
                'label_en' => 'Site Logo (Dark Mode)',
                'label_ar' => 'شعار الموقع (الوضع الداكن)',
                'description_en' => 'Upload your website logo for dark mode (optional)',
                'description_ar' => 'رفع شعار موقعك الإلكتروني للوضع الداكن (اختياري)',
                'is_required' => false,
                'display_order' => 4,
            ],
            [
                'key' => 'facebook_pixel_url',
                'group' => 'analytics',
                'type' => 'text',
                'value' => null,
                'label_en' => 'Facebook Pixel URL',
                'label_ar' => 'رابط بكسل فيسبوك',
                'description_en' => 'Facebook Pixel tracking URL for analytics',
                'description_ar' => 'رابط بكسل فيسبوك لتتبع الإحصائيات',
                'is_required' => false,
                'display_order' => 5,
            ],
            [
                'key' => 'facebook_pixel_id',
                'group' => 'analytics',
                'type' => 'text',
                'value' => null,
                'label_en' => 'Facebook Pixel ID',
                'label_ar' => 'معرف بكسل فيسبوك',
                'description_en' => 'Facebook Pixel ID for tracking',
                'description_ar' => 'معرف بكسل فيسبوك للتتبع',
                'is_required' => false,
                'display_order' => 6,
            ],
            [
                'key' => 'maintenance_mode',
                'group' => 'general',
                'type' => 'boolean',
                'value' => '0',
                'label_en' => 'Maintenance Mode',
                'label_ar' => 'وضع الصيانة',
                'description_en' => 'Enable maintenance mode for the website',
                'description_ar' => 'تفعيل وضع الصيانة للموقع',
                'is_required' => false,
                'display_order' => 7,
            ],
            [
                'key' => 'contact_email',
                'group' => 'contact',
                'type' => 'text',
                'value' => 'contact@vilain.com',
                'label_en' => 'Contact Email',
                'label_ar' => 'بريد التواصل',
                'description_en' => 'Main contact email address',
                'description_ar' => 'عنوان البريد الإلكتروني الرئيسي للتواصل',
                'is_required' => true,
                'display_order' => 8,
            ],
            [
                'key' => 'social_links',
                'group' => 'social',
                'type' => 'json',
                'value' => json_encode([
                    'facebook' => '',
                    'twitter' => '',
                    'instagram' => '',
                    'linkedin' => '',
                ]),
                'label_en' => 'Social Media Links',
                'label_ar' => 'روابط وسائل التواصل الاجتماعي',
                'description_en' => 'Social media profile links',
                'description_ar' => 'روابط ملفات وسائل التواصل الاجتماعي',
                'is_required' => false,
                'display_order' => 9,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::createOrFirst(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
