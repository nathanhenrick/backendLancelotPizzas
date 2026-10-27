<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'senha' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email'    => 'Insira um endereço de e-mail válido.',
            'senha.required' => 'O campo senha é obrigatório.',
        ];
    }
}
