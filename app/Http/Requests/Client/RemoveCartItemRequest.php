<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class RemoveCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_item_id' => 'required|exists:cart_items,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'cart_item_id' => 'Sản phẩm trong giỏ hàng',
        ];
    }
}
