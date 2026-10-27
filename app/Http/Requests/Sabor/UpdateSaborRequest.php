<?php

namespace App\Http\Requests\Sabor;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaborRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'           => 'sometimes|string|max:80',
            'descricao'      => 'sometimes|nullable|string|max:255',
            'tipo'           => 'sometimes|in:salgada,doce',
            'faixa'          => ['sometimes', 'string', Rule::exists('faixas', 'codigo')],
            'ingredientes'   => 'sometimes|array|max:20',
            'ingredientes.*' => 'string|max:30|distinct',
            'adicionais'     => 'sometimes|array|max:30',
            'adicionais.*'   => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'ativo'          => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in'      => 'Escolha salgada ou doce.',
            'faixa.exists' => 'A faixa de preço selecionada é inválida.',
            'max'          => 'O campo :attribute não pode ser maior que :max.',
        ];
    }
}
