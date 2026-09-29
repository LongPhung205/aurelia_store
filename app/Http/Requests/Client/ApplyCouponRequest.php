<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'coupon_code' => 'Mã giảm giá',
            'subtotal' => 'Tổng tiền tạm tính',
        ];
    }
}
