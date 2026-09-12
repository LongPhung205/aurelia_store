<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variants' => 'required|array|min:1',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'required|numeric|min:0',
            'color_images' => 'nullable|array',
            'color_images.*' => 'image|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        // SKU auto-generation is moved to the controller for bulk creation
    }
}

