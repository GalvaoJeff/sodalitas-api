<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_.]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'bio' => ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:4096'], // até 4MB
            'birthdate' => ['sometimes', 'nullable', 'date', 'before:today'],
            'location' => ['sometimes', 'nullable', 'string', 'max:120'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:120'],
            'education' => ['sometimes', 'nullable', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'show_phone' => ['sometimes', 'boolean'],
            'hobbies' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'O username só pode conter letras, números, ponto (.), traço (-) e underscore (_).',
            'username.unique' => 'Esse username já está em uso.',
            'username.max' => 'O username pode ter no máximo 50 caracteres.',
            'bio.max' => 'A bio pode ter no máximo 500 caracteres.',
            'avatar.image' => 'O arquivo enviado precisa ser uma imagem.',
            'avatar.max' => 'A imagem pode ter no máximo 4MB.',
            'birthdate.date' => 'Informe uma data válida.',
            'birthdate.before' => 'A data de nascimento precisa ser anterior a hoje.',
        ];
    }
}
