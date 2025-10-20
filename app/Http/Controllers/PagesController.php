<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('Pages/Privacy', [
            'content' => [
                'en' => SettingsService::get('privacy_policy_en'),
                'ar' => SettingsService::get('privacy_policy_ar'),
            ],
            'title' => [
                'en' => 'Privacy Policy',
                'ar' => 'سياسة الخصوصية',
            ],
        ]);
    }

    /**
     * Display the return policy page.
     */
    public function returns(): Response
    {
        return Inertia::render('Pages/Returns', [
            'content' => [
                'en' => SettingsService::get('return_policy_en'),
                'ar' => SettingsService::get('return_policy_ar'),
            ],
            'title' => [
                'en' => 'Return Policy',
                'ar' => 'سياسة الإرجاع',
            ],
        ]);
    }

    /**
     * Display the terms of service page.
     */
    public function terms(): Response
    {
        return Inertia::render('Pages/Terms', [
            'content' => [
                'en' => SettingsService::get('terms_of_service_en'),
                'ar' => SettingsService::get('terms_of_service_ar'),
            ],
            'title' => [
                'en' => 'Terms of Service',
                'ar' => 'شروط الخدمة',
            ],
        ]);
    }

    /**
     * Display the contact page.
     */
    public function contact(): Response
    {
        return Inertia::render('Pages/Contact', [
            'content' => [
                'en' => SettingsService::get('contact_page_en'),
                'ar' => SettingsService::get('contact_page_ar'),
            ],
            'title' => [
                'en' => 'Contact Us',
                'ar' => 'اتصل بنا',
            ],
        ]);
    }

    /**
     * Display the exchange policy page.
     */
    public function exchangePolicy(): Response
    {
        return Inertia::render('Pages/ExchangePolicy', [
            'content' => [
                'en' => SettingsService::get('exchange_policy_en'),
                'ar' => SettingsService::get('exchange_policy_ar'),
            ],
            'title' => [
                'en' => 'Exchange Policy',
                'ar' => 'سياسة الاستبدال',
            ],
        ]);
    }

    /**
     * Display the about us page.
     */
    public function aboutUs(): Response
    {
        return Inertia::render('Pages/AboutUs', [
            'content' => [
                'en' => SettingsService::get('about_us_en'),
                'ar' => SettingsService::get('about_us_ar'),
            ],
            'title' => [
                'en' => 'About Us',
                'ar' => 'من نحن',
            ],
        ]);
    }

    /**
     * Display the delivery policy page.
     */
    public function deliveryPolicy(): Response
    {
        return Inertia::render('Pages/DeliveryPolicy', [
            'content' => [
                'en' => SettingsService::get('delivery_policy_en'),
                'ar' => SettingsService::get('delivery_policy_ar'),
            ],
            'title' => [
                'en' => 'Delivery Policy',
                'ar' => 'سياسة التسليم',
            ],
        ]);
    }

    /**
     * Display the shipping policy page.
     */
    public function shippingPolicy(): Response
    {
        return Inertia::render('Pages/ShippingPolicy', [
            'content' => [
                'en' => SettingsService::get('shipping_policy_en'),
                'ar' => SettingsService::get('shipping_policy_ar'),
            ],
            'title' => [
                'en' => 'Shipping Policy',
                'ar' => 'سياسة الشحن',
            ],
        ]);
    }

    /**
     * Display the Facebook data deletion instructions page.
     */
    public function facebookDataDeletion(): Response
    {
        return Inertia::render('Pages/FacebookDataDeletion');
    }
}
