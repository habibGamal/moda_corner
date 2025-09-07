<?php

namespace App\Models;

use App\Enums\PromotionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'code',
        'description_en',
        'description_ar',
        'type',
        'value',
        'min_order_value',
        'usage_limit',
        'usage_count',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'type' => PromotionType::class,
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'float',
        'min_order_value' => 'float',
    ];

    /**
     * Get the conditions for the promotion.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(PromotionCondition::class);
    }

    /**
     * Get the rewards for the promotion.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(PromotionReward::class);
    }

    /**
     * Get the usages of this promotion.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Get the orders that used this promotion.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if the promotion is valid at the current time.
     */
    public function isValid(): bool
    {
        $now = now();

        // Check if promotion is active
        if (!$this->is_active) {
            return false;
        }

        // Check if promotion has reached usage limit
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        // Check if promotion has started
        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        // Check if promotion has expired
        if ($this->expires_at !== null && $now->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this promotion provides free shipping.
     */
    public function isFreeShipping(): bool
    {
        return $this->type === PromotionType::FREE_SHIPPING;
    }
}
