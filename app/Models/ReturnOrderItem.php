<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_order_id',
        'order_item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($returnOrderItem) {
            if (! $returnOrderItem->subtotal && $returnOrderItem->quantity && $returnOrderItem->unit_price) {
                $returnOrderItem->subtotal = $returnOrderItem->quantity * $returnOrderItem->unit_price;
            }
        });

        static::updating(function ($returnOrderItem) {
            if ($returnOrderItem->isDirty(['quantity', 'unit_price'])) {
                $returnOrderItem->subtotal = $returnOrderItem->quantity * $returnOrderItem->unit_price;
            }
        });
    }

    /**
     * Get the return order that owns this item.
     */
    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    /**
     * Get the original order item.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product through the order item.
     */
    public function product(): BelongsTo
    {
        return $this->orderItem()->belongsTo(Product::class);
    }

    /**
     * Get the product variant through the order item.
     */
    public function variant(): BelongsTo
    {
        return $this->orderItem()->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Check if this return item quantity is valid (not exceeding the original order item quantity).
     */
    public function isValidQuantity(): bool
    {
        return $this->quantity <= $this->orderItem->quantity;
    }

    /**
     * Get the maximum returnable quantity for this item.
     */
    public function getMaxReturnableQuantity(): int
    {
        $orderItem = $this->orderItem;
        $alreadyReturnedQuantity = static::where('order_item_id', $orderItem->id)
            ->whereHas('returnOrder', function ($query) {
                $query->whereIn('status', ['approved', 'completed']);
            })
            ->sum('quantity');

        return $orderItem->quantity - $alreadyReturnedQuantity;
    }
}
