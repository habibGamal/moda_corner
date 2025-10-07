<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'image',
        'cta_link',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the title based on the current locale.
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $column = "title_{$locale}";

        return $this->{$column};
    }

    /**
     * Get the description based on the current locale.
     */
    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $column = "description_{$locale}";

        return $this->{$column};
    }
}
