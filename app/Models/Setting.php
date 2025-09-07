<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group',
        'type',
        'value',
        'label_en',
        'label_ar',
        'description_en',
        'description_ar',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the value cast to appropriate type
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return match ($this->type) {
                    'boolean' => (bool) $value,
                    'json' => json_decode($value, true),
                    'integer' => (int) $value,
                    'float' => (float) $value,
                    default => $value,
                };
            },
            set: function ($value) {
                return match ($this->type) {
                    'boolean' => $value ? '1' : '0',
                    'json' => is_array($value) ? json_encode($value) : $value,
                    default => (string) $value,
                };
            }
        );
    }

    /**
     * Get setting by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
            return true;
        }

        return false;
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->orderBy('display_order')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })
            ->toArray();
    }

    /**
     * Get localized label
     */
    public function getLocalizedLabel(): string
    {
        $locale = app()->getLocale();
        return $this->{"label_{$locale}"} ?? $this->label_en;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescription(): ?string
    {
        $locale = app()->getLocale();
        return $this->{"description_{$locale}"} ?? $this->description_en;
    }

    /**
     * Scope to filter by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('label_en');
    }
}
