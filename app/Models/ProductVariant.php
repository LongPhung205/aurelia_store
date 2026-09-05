<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'sku', 'barcode', 
        'price', 'sale_price', 'cost_price', 'stock_quantity', 
        'low_stock_threshold', 'weight_grams', 
        'thumbnail_url', 'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function image()
    {
        return $this->hasOne(ProductImage::class, 'variant_id');
    }
}
