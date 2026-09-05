<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:255',
            'status' => 'required|in:active,hidden',
            // Variants validation
            'variants' => 'nullable|array',
            'variants.*.color_id' => 'required_with:variants|exists:colors,id',
            'variants.*.size_id' => 'required_with:variants|exists:sizes,id',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.sku' => 'required_with:variants|string|max:100|distinct|unique:product_variants,sku',
            
            // Color Images validation
            'color_images' => 'nullable|array',
            'color_images.*' => 'image|mimes:jpeg,png,jpg,webp,gif,svg|max:2048',
        ];
    }
    
    protected function prepareForValidation()
    {
        if (!$this->has('variants') || empty($this->variants)) {
            // Handle Simple Product: Auto-generate a default variant
            $skuParts = [\Illuminate\Support\Str::slug($this->name), 'DEFAULT'];
            $this->merge([
                'variants' => [
                    'default' => [
                        'color_id' => null,
                        'size_id' => null,
                        'price' => 0,
                        'sku' => strtoupper(implode('-', array_filter($skuParts))),
                    ]
                ]
            ]);
        } else {
            $variants = $this->variants;
            foreach ($variants as $key => &$variant) {
                if (empty($variant['sku'])) {
                    $color = \App\Models\Color::find($variant['color_id'] ?? null);
                    $size = \App\Models\Size::find($variant['size_id'] ?? null);
                    
                    $skuParts = [];
                    if ($this->name) $skuParts[] = \Illuminate\Support\Str::slug($this->name);
                    if ($color) $skuParts[] = \Illuminate\Support\Str::slug($color->name);
                    if ($size) $skuParts[] = \Illuminate\Support\Str::slug($size->name);
                    
                    if (!empty($skuParts)) {
                        $variant['sku'] = strtoupper(implode('-', $skuParts));
                    } else {
                        $variant['sku'] = strtoupper(uniqid('SKU-'));
                    }
                }
            }
            $this->merge(['variants' => $variants]);
        }
    }
}

