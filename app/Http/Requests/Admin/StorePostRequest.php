<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Tiêu đề bài viết',
            'excerpt' => 'Mô tả ngắn',
            'content' => 'Nội dung bài viết',
            'image' => 'Ảnh đại diện bài viết',
            'is_active' => 'Trạng thái phát hành',
        ];
    }
}
