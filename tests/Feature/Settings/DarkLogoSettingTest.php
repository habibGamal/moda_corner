<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DarkLogoSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the dark logo setting
        Setting::factory()->create([
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
        ]);
    }

    public function test_dark_logo_setting_exists(): void
    {
        $setting = Setting::where('key', 'site_logo_dark')->first();

        expect($setting)->not->toBeNull()
            ->and($setting->key)->toBe('site_logo_dark')
            ->and($setting->type)->toBe('image')
            ->and($setting->group)->toBe('general');
    }

    public function test_dark_logo_can_be_retrieved_from_settings_service(): void
    {
        Setting::where('key', 'site_logo_dark')->first()->update([
            'value' => 'logos/dark-logo.png',
        ]);

        $darkLogo = SettingsService::get('site_logo_dark');

        expect($darkLogo)->toBe('logos/dark-logo.png');
    }

    public function test_site_config_includes_dark_logo(): void
    {
        Setting::where('key', 'site_logo_dark')->first()->update([
            'value' => 'logos/dark-logo.png',
        ]);

        $config = SettingsService::getSiteConfig();

        expect($config)->toHaveKey('site_logo_dark')
            ->and($config['site_logo_dark'])->toBe('logos/dark-logo.png');
    }

    public function test_dark_logo_can_be_null_or_empty(): void
    {
        $config = SettingsService::getSiteConfig();

        expect($config)->toHaveKey('site_logo_dark')
            ->and($config['site_logo_dark'])->toBeIn([null, '']);
    }

    public function test_dark_logo_setting_can_be_updated(): void
    {
        $setting = Setting::where('key', 'site_logo_dark')->first();
        $setting->update(['value' => 'logos/new-dark-logo.png']);

        $darkLogo = SettingsService::get('site_logo_dark');

        expect($darkLogo)->toBe('logos/new-dark-logo.png');
    }
}
