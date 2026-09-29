<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'category_id',
        'image_url',
        'link',
        'position',
        'is_active',
    ];

    /**
     * Get the category associated with this banner.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope for Home slider banners.
     */
    public function scopeHome($query)
    {
        return $query->where(function($q) {
            $q->where('type', 'home_slider')->orWhereNull('type');
        })->whereNull('category_id');
    }

    /**
     * Scope for Category header banners.
     */
    public function scopeCategoryHeader($query)
    {
        return $query->where('type', 'category_header');
    }

    /**
     * Get the full URL for the banner image.
     */
    public function getDisplayImageUrlAttribute()
    {
        $imgUrl = $this->image_url;
        
        if (!$imgUrl) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($imgUrl, ['http://', 'https://'])) {
            return $imgUrl;
        }

        if (\Illuminate\Support\Str::startsWith($imgUrl, 'images/')) {
            return asset($imgUrl);
        }

        return asset('storage/' . $imgUrl);
    }
}
