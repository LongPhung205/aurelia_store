<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');
        $couponId = is_object($coupon) ? $coupon->id : $coupon;

        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . $couponId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Mã giảm giá',
            'name' => 'Tên chương trình',
            'type' => 'Loại giảm giá',
            'value' => 'Giá trị giảm',
            'min_order_value' => 'Đơn tối thiểu',
            'max_discount' => 'Giảm tối đa',
            'start_time' => 'Thời gian bắt đầu',
            'end_time' => 'Thời gian kết thúc',
            'usage_limit' => 'Lượt dùng tối đa',
            'usage_limit_per_user' => 'Lượt dùng / khách hàng',
        ];
    }
}
