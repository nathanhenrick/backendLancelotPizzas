<?php

namespace App\Http\Requests\Sabor;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaborRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'           => 'required|string|max:80',
            'descricao'      => 'nullable|string|max:255',
            'tipo'           => 'nullable|in:salgada,doce',
            'faixa'          => ['required', 'string', Rule::exists('faixas', 'codigo')],
            'ingredientes'   => 'present|array|max:20',
            'ingredientes.*' => 'string|max:30|distinct',
            'adicionais'     => 'nullable|array|max:30',
            'adicionais.*'   => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'ativo'          => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'required'     => 'O campo :attribute é obrigatório.',
            'tipo.in'      => 'Escolha salgada ou doce.',
            'faixa.exists' => 'A faixa de preço selecionada é inválida.',
            'max'          => 'O campo :attribute não pode ser maior que :max.',
        ];
    }
}
