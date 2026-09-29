<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddFlashSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_products' => 'required|array|min:1',
            'selected_products.*' => 'exists:products,id',
            'products' => 'required|array',
            'products.*.flash_sale_price' => 'nullable|numeric|min:0',
            'products.*.quantity' => 'nullable|integer|min:1',
        ];
    }

    public function attributes(): array
    {
        return [
            'selected_products' => 'Sản phẩm chọn',
            'products' => 'Thông tin giá và số lượng Flash Sale',
        ];
    }
}
