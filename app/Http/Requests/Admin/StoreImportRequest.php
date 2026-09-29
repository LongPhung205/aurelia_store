<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|string',
            'note' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*' => 'exists:product_variants,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'supplier_id' => 'Nhà cung cấp',
            'note' => 'Ghi chú',
            'variants' => 'Danh sách biến thể',
            'quantities' => 'Số lượng nhập',
            'prices' => 'Đơn giá nhập',
        ];
    }
}
