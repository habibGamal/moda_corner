<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectPromotion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'type',
        'discount_percentage',
        'apply_to',
        'category_id',
        'brand_id',
        'minimum_order_amount',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the category for category-specific promotions.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand for brand-specific promotions.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Check if the promotion is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this is a price discount promotion.
     */
    public function isPriceDiscount(): bool
    {
        return $this->type === 'price_discount';
    }

    /**
     * Check if this is a free shipping promotion.
     */
    public function isFreeShipping(): bool
    {
        return $this->type === 'free_shipping';
    }

    /**
     * Get the display name based on current locale.
     */
    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }

    /**
     * Get the display description based on current locale.
     */
    public function getDisplayDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Scope to get only active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only valid promotions (active and within time range).
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope to get price discount promotions.
     */
    public function scopePriceDiscount($query)
    {
        return $query->where('type', 'price_discount');
    }

    /**
     * Scope to get free shipping promotions.
     */
    public function scopeFreeShipping($query)
    {
        return $query->where('type', 'free_shipping');
    }
}
