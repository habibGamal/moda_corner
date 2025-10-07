<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(\App\Observers\ImageCleanupObserver::class)]
class Brand extends Model
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
        'slug',
        'image',
        'is_active',
        'parent_id',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'display_image',
        'image_url',
    ];

    /**
     * Get the products for the brand.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the parent brand.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'parent_id');
    }

    /**
     * Get the child brands.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Brand::class, 'parent_id');
    }

    /**
     * Get the display image with fallback to first product's featured image.
     */
    public function getDisplayImageAttribute(): ?string
    {
        // Return the brand's own image if it exists
        if (! empty($this->image)) {
            return $this->image;
        }

        // Fallback to first product's featured image
        $firstProduct = $this->products()
            ->where('is_active', true)
            ->first();

        return $firstProduct?->featured_image;
    }

    /**
     * Get the image URL with fallback logic.
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->display_image;

        if (! $image) {
            return null;
        }

        // Return full URL for external images or storage path for local images
        if (str_starts_with($image, 'http')) {
            return $image;
        }

        return asset('storage/'.$image);
    }
}
