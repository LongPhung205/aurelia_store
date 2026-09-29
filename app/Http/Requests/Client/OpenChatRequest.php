<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class OpenChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => 'nullable|exists:orders,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'order_id' => 'Mã đơn hàng liên quan',
        ];
    }
}
