<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,success,failed'],
            'note'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
