<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'order_id',
        'user_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'float',
    ];

    /**
     * Get the promotion that was used.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the order where the promotion was applied.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who used the promotion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
