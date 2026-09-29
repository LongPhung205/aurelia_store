<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:complete,cancel',
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'Hành động phiếu nhập',
        ];
    }
}
