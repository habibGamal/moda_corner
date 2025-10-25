<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    /**
     * TNTSearch configuration.
     */
    // public $asYouType = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'description_en',
        'description_ar',
        'price',
        'sale_price',
        'cost_price',
        'category_id',
        'brand_id',
        'is_active',
        'is_featured',
        'special_label',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'featured_image',
        'is_in_wishlist',
        'average_rating',
        'reviews_count',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $this->load(['brand', 'category']);

        $array = [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->normalizeArabic($this->name_ar),
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'slug' => $this->slug,
            'brand_name_en' => $this->brand?->name_en,
            'brand_name_ar' => $this->brand?->name_ar,
            'category_name_en' => $this->category?->name_en,
            'category_name_ar' => $this->category?->name_ar,
        ];

        return $array;
    }

    /**
     * Normalize Arabic letters for search consistency.
     *
     * @param  string|null  $text
     * @return string|null
     */
    protected function normalizeArabic($text)
    {
        if (! $text) {
            return $text;
        }
        $text = trim($text);
        $text = mb_strtolower($text, 'UTF-8');
        // Normalize common Arabic letter variations
        $search = [
            'أ',
            'إ',
            'آ',
            'ى',
            'ئ',
            'ؤ',
            'ة',
            'ٱ',
            'ء',
        ];
        $replace = [
            'ا',
            'ا',
            'ا',
            'ي',
            'ي',
            'و',
            'ه',
            'ا',
            '',
        ];

        return str_replace($search, $replace, $text);
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the wishlist items for this product.
     */
    public function wishlists()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Get the reviews for this product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Check if the product is in the wishlist of the given user.
     *
     * @param  int|null  $userId
     * @return bool
     */
    public function getIsInWishlistAttribute($userId = null)
    {
        if (! $userId && ! auth()->check()) {
            return false;
        }

        $userId = $userId ?? auth()->id();

        return $this->wishlists()->where('user_id', $userId)->exists();
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the sections for the product.
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_product')
            ->withTimestamps();
    }

    /**
     * Get the default variant for the product.
     */
    public function defaultVariant()
    {
        return $this->variants()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first() ?: $this->variants()->where('is_active', true)->first();
    }

    /**
     * Get the total quantity across all variants.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->variants->sum('quantity');
    }

    /**
     * Get the featured image from the default variant or first variant.
     */
    public function getFeaturedImageAttribute()
    {
        // Try to get image from default variant
        $defaultVariant = $this->defaultVariant();
        if ($defaultVariant && ! empty($defaultVariant->images)) {
            return $defaultVariant->featured_image;
        }

        // If no default variant, try the first variant with images
        $variantWithImages = $this->variants->filter(function ($variant) {
            return ! empty($variant->images);
        })->first();

        return $variantWithImages ? $variantWithImages->featured_image : null;
    }

    /**
     * Get all images from all variants.
     */
    public function getAllImagesAttribute(): array
    {
        $images = [];
        foreach ($this->variants as $variant) {
            if (! empty($variant->images)) {
                $images = array_merge($images, $variant->images);
            }
        }

        return array_unique($images);
    }

    /**
     * Check if the product has any variants in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->variants->where('quantity', '>', 0)->count() > 0;
    }

    public function scopeForCards()
    {
        return $this->where('is_active', true)
            ->with([
                'brand' => function ($query) {
                    $query->select('id', 'name_en', 'name_ar', 'slug', 'image');
                },
                'variants',
            ]);
    }

    /**
     * Get the average rating for the product.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->approved()->avg('rating') ?? 0, 1);
    }

    /**
     * Get the reviews count for the product.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->approved()->count();
    }
}
