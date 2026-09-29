<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => 'Đánh giá sao',
            'content' => 'Nội dung nhận xét',
            'images' => 'Hình ảnh đánh giá',
        ];
    }
}
