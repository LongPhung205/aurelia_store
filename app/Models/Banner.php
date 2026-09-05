<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_url',
        'link',
        'position',
        'is_active',
    ];

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
