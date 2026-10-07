<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'short_description', 
        'description', 'material', 'status', 'view_count'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('display_order');
    }

    public function getPrimaryImageUrlAttribute()
    {
        if ($this->primaryImage) {
            return $this->primaryImage->image_url;
        }

        $variant = $this->variants->whereNotNull('thumbnail_url')->first();
        if ($variant) {
            return $variant->thumbnail_url;
        }

        return null;
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?: 5;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

}
