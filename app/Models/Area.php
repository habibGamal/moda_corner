<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Area extends Model
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
        'gov_id',
    ];

    /**
     * Get the governorate that owns the area.
     */
    public function gov(): BelongsTo
    {
        return $this->belongsTo(Gov::class);
    }

    /**
     * Get the shipping costs for the area.
     */
    public function shippingCosts(): HasMany
    {
        return $this->hasMany(ShippingCost::class);
    }

    /**
     * Get the single shipping cost for the area.
     */
    public function shippingCost(): HasOne
    {
        return $this->hasOne(ShippingCost::class);
    }

    /**
     * Get the addresses for the area.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
