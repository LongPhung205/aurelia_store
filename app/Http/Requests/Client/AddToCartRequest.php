<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function attributes(): array
    {
        return [
            'variant_id' => 'Biến thể sản phẩm',
            'quantity' => 'Số lượng',
        ];
    }
}
