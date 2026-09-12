<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'address',
        'province_id',
        'district_id',
        'ward_code',
        'subtotal',
        'shipping_fee',
        'discount',
        'total_amount',
        'shipping_order_code',
        'shipping_status',
        'status',
        'payment_method',
        'payment_status',
        'is_inventory_deducted',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
