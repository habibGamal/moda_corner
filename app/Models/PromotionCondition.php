<?php

namespace App\Models;

use App\Enums\PromotionConditionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'type',
        'entity_id',
        'quantity',
    ];

    protected $casts = [
        'type' => PromotionConditionType::class,
    ];

    /**
     * Get the promotion that owns the condition.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the related entity based on the condition type.
     */
    public function getRelatedEntity()
    {
        return match ($this->type) {
            PromotionConditionType::PRODUCT => Product::find($this->entity_id),
            PromotionConditionType::CATEGORY => Category::find($this->entity_id),
            PromotionConditionType::BRAND => Brand::find($this->entity_id),
            PromotionConditionType::CUSTOMER => User::find($this->entity_id),
            default => null,
        };
    }
}
