<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:2200'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:51200'], // até 50MB
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'caption.max' => 'A legenda pode ter no máximo 2200 caracteres.',
            'media.array' => 'Envie as mídias em um formato válido.',
            'media.max' => 'Você pode enviar no máximo 10 arquivos por publicação.',
            'media.*.file' => 'Um dos arquivos falhou ao ser enviado. Verifique se ele não excede o tamanho máximo permitido pelo servidor.',
            'media.*.uploaded' => 'Um dos arquivos falhou ao ser enviado — ele pode ser maior do que o limite configurado no servidor (php.ini) ou o envio foi interrompido.',
            'media.*.mimes' => 'Formato não suportado. Envie imagens (JPG, PNG, WEBP) ou vídeos (MP4, MOV).',
            'media.*.max' => 'Cada arquivo pode ter no máximo 50MB.',
        ];
    }
}
