<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'response_data',
        'admin_id',
        'note',
        'reconciled_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'reconciled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
