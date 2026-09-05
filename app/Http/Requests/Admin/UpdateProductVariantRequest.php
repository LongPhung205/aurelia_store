<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = $this->route('variant');
        return [
            'color_id' => 'nullable|exists:colors,id',
            'size_id' => 'nullable|exists:sizes,id',
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('product_variants')->ignore($variant)
            ],
            'barcode' => [
                'nullable', 'string', 'max:100',
                Rule::unique('product_variants')->ignore($variant)
            ],
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'thumbnail_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        if (empty($this->sku)) {
            $product = $this->route('product');
            $color = \App\Models\Color::find($this->color_id);
            $size = \App\Models\Size::find($this->size_id);
            
            $skuParts = [];
            if ($product) $skuParts[] = \Illuminate\Support\Str::slug($product->name);
            if ($color) $skuParts[] = \Illuminate\Support\Str::slug($color->name);
            if ($size) $skuParts[] = \Illuminate\Support\Str::slug($size->name);
            
            if (!empty($skuParts)) {
                $this->merge([
                    'sku' => strtoupper(implode('-', $skuParts))
                ]);
            }
        }
    }
}

