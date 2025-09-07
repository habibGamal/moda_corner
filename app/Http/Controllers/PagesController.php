<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{    /**
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
    }    /**
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
    }    /**
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
     * Display the Facebook data deletion instructions page.
     */
    public function facebookDataDeletion(): Response
    {
        return Inertia::render('Pages/FacebookDataDeletion');
    }
}
