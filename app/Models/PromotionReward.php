<?php

namespace App\Models;

use App\Enums\PromotionRewardType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'type',
        'entity_id',
        'quantity',
        'discount_percentage',
    ];

    protected $casts = [
        'type' => PromotionRewardType::class,
        'discount_percentage' => 'float',
    ];

    /**
     * Get the promotion that owns the reward.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the related entity based on the reward type.
     */
    public function getRelatedEntity()
    {
        return match ($this->type) {
            PromotionRewardType::PRODUCT => Product::find($this->entity_id),
            PromotionRewardType::CATEGORY => Category::find($this->entity_id),
            PromotionRewardType::BRAND => Brand::find($this->entity_id),
            default => null,
        };
    }
}
