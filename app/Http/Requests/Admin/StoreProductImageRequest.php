<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'images' => 'Danh sách hình ảnh',
            'images.*' => 'Tệp hình ảnh',
        ];
    }
}
