<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_.]+$/',
                'unique:users,username',
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'username.required' => 'O username é obrigatório.',
            'username.regex' => 'O username só pode conter letras, números, ponto (.), traço (-) e underscore (_).',
            'username.unique' => 'Esse username já está em uso.',
            'username.max' => 'O username pode ter no máximo 50 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Esse e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.min' => 'A senha precisa ter no mínimo 8 caracteres.',
            'password.letters' => 'A senha precisa conter pelo menos uma letra.',
            'password.numbers' => 'A senha precisa conter pelo menos um número.',
            'password.symbols' => 'A senha precisa conter pelo menos um caractere especial (ex: ! @ # $ %).',
        ];
    }
}
