<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_code' => 'required|string',
            'payment_method' => 'required|in:cod,payos,momo',
            'coupon_code' => 'nullable|string',
            'note' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'Họ và tên người nhận',
            'customer_phone' => 'Số điện thoại',
            'address' => 'Địa chỉ nhận hàng',
            'province_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'ward_code' => 'Phường/Xã',
            'payment_method' => 'Phương thức thanh toán',
            'coupon_code' => 'Mã giảm giá',
            'note' => 'Ghi chú đơn hàng',
        ];
    }
}
