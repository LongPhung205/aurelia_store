<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên chương trình Flash Sale',
            'start_time' => 'Thời gian bắt đầu',
            'end_time' => 'Thời gian kết thúc',
            'is_active' => 'Trạng thái hoạt động',
        ];
    }
}
