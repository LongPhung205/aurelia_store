<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['name', 'weight_range'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
