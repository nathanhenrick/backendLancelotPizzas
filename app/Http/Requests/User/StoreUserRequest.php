<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'   => 'required|string|max:120',
            'email'  => 'required|string|email|max:255|unique:users,email',
            'senha'  => 'required|string|min:8|max:72',
            'status' => 'required|in:ATIVO,INATIVO',
            'papel'  => 'sometimes|in:dono,atendente',
        ];
    }

    public function messages(): array
    {
        return [
            'required'     => 'O campo :attribute é obrigatório.',
            'email.email'  => 'Insira um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'papel.in'     => 'O papel deve ser dono ou atendente.',
            'senha.min'    => 'A senha deve ter pelo menos :min caracteres.',
            'status.in'    => 'O status deve ser ATIVO ou INATIVO.',
        ];
    }
}
