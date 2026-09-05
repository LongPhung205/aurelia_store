<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_order_value',
        'max_discount',
        'start_time',
        'end_time',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid($orderValue = 0)
    {
        if (!$this->is_active) return false;
        if (now()->lt($this->start_time) || now()->gt($this->end_time)) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;
        if ($orderValue < $this->min_order_value) return false;

        return true;
    }
}
