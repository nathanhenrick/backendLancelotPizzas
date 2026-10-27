<?php

namespace App\Http\Requests\Adicional;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdicionalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|string|max:60',
            'preco' => 'sometimes|numeric|min:0|max:9999',
            'ativo' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'preco.numeric' => 'O preço deve ser um número.',
            'preco.min'     => 'O preço não pode ser negativo.',
        ];
    }
}
