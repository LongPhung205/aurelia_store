<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:home_slider,category_header',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|string|max:255',
            'position' => 'nullable|integer',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Tiêu đề',
            'type' => 'Loại Banner / Vị trí áp dụng',
            'category_id' => 'Danh mục sản phẩm',
            'image' => 'Hình ảnh banner',
            'link' => 'Đường dẫn liên kết',
            'position' => 'Vị trí hiển thị',
            'is_active' => 'Trạng thái hiển thị',
        ];
    }
}
