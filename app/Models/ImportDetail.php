<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
