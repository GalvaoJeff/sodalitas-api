<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddHighlightStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'story_id' => ['required', 'integer', 'exists:stories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'story_id.required' => 'Selecione uma story para adicionar ao destaque.',
            'story_id.exists' => 'Essa story não existe ou já expirou.',
        ];
    }
}
