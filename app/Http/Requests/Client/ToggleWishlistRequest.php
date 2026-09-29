<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class ToggleWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'Sản phẩm yêu thích',
        ];
    }
}
