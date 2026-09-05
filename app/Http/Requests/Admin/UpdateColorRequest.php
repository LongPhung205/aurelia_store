<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $color = $this->route('color');
        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('colors')->ignore($color)
            ],
            'hex_code' => 'required|string|max:10',
        ];
    }
}

