<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
