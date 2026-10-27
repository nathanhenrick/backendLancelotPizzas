<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'nome'   => 'sometimes|string|max:120',
            'email'  => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'senha'  => 'sometimes|nullable|string|min:8|max:72',
            'status' => 'sometimes|in:ATIVO,INATIVO',
            'papel'  => 'sometimes|in:dono,atendente',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'  => 'Insira um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'senha.min'    => 'A senha deve ter pelo menos :min caracteres.',
            'status.in'    => 'O status deve ser ATIVO ou INATIVO.',
            'papel.in'     => 'O papel deve ser dono ou atendente.',
        ];
    }
}
