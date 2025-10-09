<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache key for settings
     */
    private const CACHE_KEY = 'app_settings';

    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_DURATION = 3600;

    /**
     * Get all settings grouped by group
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY.'_all', self::CACHE_DURATION, function () {
            return Setting::orderBy('group')
                ->orderBy('display_order')
                ->get()
                ->groupBy('group')
                ->map(function ($settings) {
                    return $settings->mapWithKeys(function ($setting) {
                        return [$setting->key => $setting->value];
                    });
                })
                ->toArray();
        });
    }

    /**
     * Get a specific setting value
     */
    public static function get(string $key, $default = null)
    {
        $allSettings = self::getAllFlat();

        return $allSettings[$key] ?? $default;
    }

    /**
     * Get all settings as a flat array
     */
    public static function getAllFlat(): array
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember(self::CACHE_KEY."_{$group}", self::CACHE_DURATION, function () use ($group) {
            return Setting::byGroup($group)
                ->ordered()
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->value];
                })
                ->toArray();
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): bool
    {
        $result = Setting::setValue($key, $value);

        if ($result) {
            self::clearCache();
        }

        return $result;
    }

    /**
     * Update multiple settings at once
     */
    public static function setMultiple(array $settings): bool
    {
        $success = true;

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            } else {
                $success = false;
            }
        }

        if ($success) {
            self::clearCache();
        }

        return $success;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY.'_all');
        Cache::forget(self::CACHE_KEY.'_flat');

        // Clear group-specific caches
        $groups = ['general', 'appearance', 'seo', 'social', 'analytics', 'contact', 'email', 'payment', 'legal'];
        foreach ($groups as $group) {
            Cache::forget(self::CACHE_KEY."_{$group}");
        }
    }

    /**
     * Get site configuration for frontend
     */
    public static function getSiteConfig(): array
    {
        return [
            'site_title' => self::get('site_title', 'Vilain'),
            'site_logo' => self::get('site_logo'),
            'site_logo_dark' => self::get('site_logo_dark'),
            'site_icon' => self::get('site_icon'),
            'maintenance_mode' => (bool) self::get('maintenance_mode', false),
            'contact_email' => self::get('contact_email'),
            'social_links' => self::get('social_links', '{}'),
            'facebook_pixel_url' => self::get('facebook_pixel_url'),
            'facebook_pixel_id' => self::get('facebook_pixel_id'),
            'show_privacy_policy' => (bool) self::get('show_privacy_policy', true),
            'show_return_policy' => (bool) self::get('show_return_policy', true),
            'show_terms_of_service' => (bool) self::get('show_terms_of_service', true),
            'show_contact_page' => (bool) self::get('show_contact_page', true),
        ];
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Get social media links
     */
    public static function getSocialLinks(): array
    {
        $links = self::get('social_links', []);

        return is_array($links) ? $links : [];
    }

    /**
     * Get analytics configuration
     */
    public static function getAnalyticsConfig(): array
    {
        return [
            'facebook_pixel_url' => self::get('facebook_pixel_url'),
            'facebook_pixel_id' => self::get('facebook_pixel_id'),
            // Add other analytics configs here
        ];
    }
}
