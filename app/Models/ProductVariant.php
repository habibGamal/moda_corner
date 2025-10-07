<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'images',
        'quantity',
        'price',
        'sale_price',
        'color',
        'size',
        'capacity',
        'additional_attributes',
        'is_default',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'additional_attributes' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the first image or null if no images.
     */
    public function getFeaturedImageAttribute(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        return is_array($this->images) ? $this->images[0] : null;
    }
}
