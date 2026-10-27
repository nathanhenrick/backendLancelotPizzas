<?php

namespace App\Http\Requests\Adicional;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdicionalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:60',
            'preco' => 'required|numeric|min:0|max:9999',
            'ativo' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'required'      => 'O campo :attribute é obrigatório.',
            'preco.numeric' => 'O preço deve ser um número.',
            'preco.min'     => 'O preço não pode ser negativo.',
        ];
    }
}
