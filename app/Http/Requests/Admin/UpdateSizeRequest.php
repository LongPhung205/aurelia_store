<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $size = $this->route('size');
        return [
            'name' => [
                'required', 'string', 'max:20',
                Rule::unique('sizes')->ignore($size)
            ],
            'weight_range' => 'nullable|string|max:50',
        ];
    }
}

