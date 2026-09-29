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
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($category && $value) {
                        $catModel = $category instanceof \App\Models\Category ? $category : \App\Models\Category::find($category);
                        if ($catModel && in_array((int)$value, $catModel->getAllDescendantIdsAndSelf())) {
                            $fail('Danh mục cha không thể là chính nó hoặc danh mục con của nó.');
                        }
                    }
                }
            ],
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }
}

