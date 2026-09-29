<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:2000',
        ];
    }

    public function attributes(): array
    {
        return [
            'conversation_id' => 'Cuộc hội thoại',
            'content' => 'Nội dung tin nhắn',
        ];
    }
}
