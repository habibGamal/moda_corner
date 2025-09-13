<?php

namespace App\Services;

use App\Models\ReturnPolicySetting;
use Illuminate\Support\Collection;

class ReturnPolicyService
{
    private static ?Collection $settings = null;

    /**
     * Get a return policy setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::loadSettings();

        return self::$settings->get($key, $default);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value): void
    {
        // JSON encode arrays and objects
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        ReturnPolicySetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Clear cache
        self::clearCache();
    }

    /**
     * Check if returns are enabled
     */
    public static function returnsEnabled(): bool
    {
        return (bool) self::get('returns_enabled', true);
    }

    /**
     * Get the return window in days
     */
    public static function returnWindowDays(): int
    {
        return (int) self::get('return_window_days', 30);
    }

    /**
     * Check if admin approval is required
     */
    public static function requiresAdminApproval(): bool
    {
        return (bool) self::get('require_admin_approval', true);
    }

    /**
     * Check if unused items are auto-approved
     */
    public static function autoApproveUnused(): bool
    {
        return (bool) self::get('auto_approve_unused', false);
    }

    /**
     * Get allowed return reasons
     */
    public static function allowedReturnReasons(): array
    {
        $reasons = self::get('allowed_return_reasons', ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'size_issue']);

        return is_array($reasons) ? $reasons : (is_string($reasons) ? json_decode($reasons, true) ?? [] : []);
    }

    /**
     * Get maximum return percentage
     */
    public static function maxReturnPercentage(): int
    {
        return (int) self::get('max_return_percentage', 100);
    }

    /**
     * Check if return fee is required
     */
    public static function requiresReturnFee(): bool
    {
        return (bool) self::get('require_return_fee', false);
    }

    /**
     * Get return fee amount
     */
    public static function returnFeeAmount(): float
    {
        return (float) self::get('return_fee_amount', 0);
    }

    /**
     * Check if email notifications are enabled
     */
    public static function emailNotificationsEnabled(): bool
    {
        return (bool) self::get('email_notifications', true);
    }

    /**
     * Check if SMS notifications are enabled
     */
    public static function smsNotificationsEnabled(): bool
    {
        return (bool) self::get('sms_notifications', false);
    }

    /**
     * Get all return policy settings
     */
    public static function all(): Collection
    {
        self::loadSettings();

        return self::$settings;
    }

    /**
     * Clear cached settings
     */
    public static function clearCache(): void
    {
        self::$settings = null;
    }

    /**
     * Load settings from database
     */
    private static function loadSettings(): void
    {
        if (self::$settings === null) {
            $dbSettings = ReturnPolicySetting::pluck('value', 'key');

            // Process JSON values
            self::$settings = $dbSettings->map(function ($value) {
                if (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                    $decoded = json_decode($value, true);

                    return $decoded !== null ? $decoded : $value;
                }

                return $value;
            });
        }
    }

    /**
     * Check if an item can be returned based on order date
     */
    public static function canReturn(\DateTime $orderDate): bool
    {
        if (! self::returnsEnabled()) {
            return false;
        }

        $returnWindow = self::returnWindowDays();
        $deadline = clone $orderDate;
        $deadline->modify("+{$returnWindow} days");

        return new \DateTime <= $deadline;
    }

    /**
     * Get return deadline for an order
     */
    public static function getReturnDeadline(\DateTime $orderDate): \DateTime
    {
        $returnWindow = self::returnWindowDays();
        $deadline = clone $orderDate;
        $deadline->modify("+{$returnWindow} days");

        return $deadline;
    }

    /**
     * Validate return reason
     */
    public static function isValidReturnReason(string $reason): bool
    {
        return in_array($reason, self::allowedReturnReasons());
    }

    /**
     * Get human-readable return reason labels
     */
    public static function getReturnReasonLabels(): array
    {
        return [
            'defective' => 'Defective/Damaged',
            'wrong_item' => 'Wrong Item Received',
            'not_as_described' => 'Not as Described',
            'changed_mind' => 'Changed Mind',
            'size_issue' => 'Size/Fit Issue',
            'quality_issue' => 'Quality Issue',
            'other' => 'Other',
        ];
    }

    /**
     * Get label for a return reason
     */
    public static function getReturnReasonLabel(string $reason): string
    {
        $labels = self::getReturnReasonLabels();

        return $labels[$reason] ?? ucfirst(str_replace('_', ' ', $reason));
    }

    /**
     * Initialize default return policy settings
     */
    public static function initializeDefaults(): void
    {
        $defaults = [
            'returns_enabled' => true,
            'return_window_days' => 30,
            'require_admin_approval' => true,
            'auto_approve_unused' => false,
            'allowed_return_reasons' => ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'size_issue'],
            'max_return_percentage' => 100,
            'require_return_fee' => false,
            'return_fee_amount' => 0,
            'email_notifications' => true,
            'sms_notifications' => false,
        ];

        foreach ($defaults as $key => $value) {
            if (! ReturnPolicySetting::where('key', $key)->exists()) {
                self::set($key, $value);
            }
        }
    }

    /**
     * Get all public settings for frontend
     */
    public static function getPublicSettings(): array
    {
        return [
            'returns_enabled' => self::returnsEnabled(),
            'return_window_days' => self::returnWindowDays(),
            'allowed_return_reasons' => self::allowedReturnReasons(),
            'return_reason_labels' => self::getReturnReasonLabels(),
            'max_return_percentage' => self::maxReturnPercentage(),
            'require_return_fee' => self::requiresReturnFee(),
            'return_fee_amount' => self::returnFeeAmount(),
        ];
    }
}
