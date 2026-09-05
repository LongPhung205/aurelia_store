<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('categories')->ignore($category)
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }
}

