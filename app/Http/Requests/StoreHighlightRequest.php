<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Dê um título para o destaque.',
            'title.max' => 'O título pode ter no máximo 60 caracteres.',
        ];
    }
}
