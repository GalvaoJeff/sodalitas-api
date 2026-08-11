<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'Envie uma imagem ou vídeo para a story.',
            'media.file' => 'O arquivo enviado é inválido.',
            'media.mimes' => 'Formato não suportado. Envie JPG, PNG, WEBP, MP4 ou MOV.',
            'media.max' => 'O arquivo é maior do que o limite permitido (20MB).',
        ];
    }
}
