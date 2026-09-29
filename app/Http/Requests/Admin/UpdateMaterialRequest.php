<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $material = $this->route('material');
        $materialId = is_object($material) ? $material->id : $material;

        return [
            'name' => 'required|string|max:255|unique:materials,name,' . $materialId,
            'description' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên chất liệu',
            'description' => 'Mô tả',
        ];
    }
}
