<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'cart_item_id' => 'Sản phẩm trong giỏ hàng',
            'quantity' => 'Số lượng',
        ];
    }
}
