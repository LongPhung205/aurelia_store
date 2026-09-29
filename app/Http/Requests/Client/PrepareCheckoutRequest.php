<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class PrepareCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:cart_items,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'selected_items' => 'Sản phẩm chọn thanh toán',
        ];
    }
}
