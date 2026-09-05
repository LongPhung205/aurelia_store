<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'guest_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Lấy tổng tiền của giỏ hàng.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->reduce(function ($carry, $item) {
            $price = $item->productVariant->sale_price ?: $item->productVariant->price;
            return $carry + ($price * $item->quantity);
        }, 0);
    }
}
