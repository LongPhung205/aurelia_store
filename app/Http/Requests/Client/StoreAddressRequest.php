<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|string',
            'district_id' => 'required|string',
            'ward_code' => 'required|string',
            'address' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên người nhận',
            'phone' => 'Số điện thoại',
            'province_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'ward_code' => 'Phường/Xã',
            'address' => 'Địa chỉ chi tiết',
            'is_default' => 'Địa chỉ mặc định',
        ];
    }
}
