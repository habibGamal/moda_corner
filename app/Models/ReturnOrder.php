<?php

namespace App\Models;

use App\Enums\ReturnOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReturnOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'return_number',
        'status',
        'reason',
        'total_amount',
        'refund_amount',
        'requested_at',
        'approved_at',
        'completed_at',
        'rejected_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ReturnOrderStatus::class,
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($returnOrder) {
            if (! $returnOrder->return_number) {
                $returnOrder->return_number = 'RET-'.Str::upper(Str::random(8));
            }
            if (! $returnOrder->requested_at) {
                $returnOrder->requested_at = now();
            }
        });
    }

    /**
     * Get the order that this return belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who requested this return.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the return items for this return.
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnOrderItem::class);
    }

    /**
     * Check if the return is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === ReturnOrderStatus::REQUESTED;
    }

    /**
     * Check if the return is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === ReturnOrderStatus::APPROVED;
    }

    /**
     * Check if the return is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === ReturnOrderStatus::COMPLETED;
    }

    /**
     * Check if the return is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === ReturnOrderStatus::REJECTED;
    }

    /**
     * Generate a unique return number.
     */
    public static function generateReturnNumber(): string
    {
        do {
            $returnNumber = 'RET-'.Str::upper(Str::random(8));
        } while (static::where('return_number', $returnNumber)->exists());

        return $returnNumber;
    }

    /**
     * Calculate total amount from return items.
     */
    public function calculateTotalAmount(): float
    {
        return $this->returnItems->sum('subtotal');
    }

    /**
     * Scope a query to only include pending returns.
     */
    public function scopePending($query)
    {
        return $query->where('status', ReturnOrderStatus::REQUESTED);
    }

    /**
     * Scope a query to only include approved returns.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', ReturnOrderStatus::APPROVED);
    }

    /**
     * Scope a query to only include completed returns.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', ReturnOrderStatus::COMPLETED);
    }
}
