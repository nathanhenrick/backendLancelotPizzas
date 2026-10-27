<?php

namespace App\Http\Requests\Esfirra;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEsfirraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'      => 'required|string|max:80',
            'descricao' => 'nullable|string|max:160',
            'preco' => 'required|numeric|min:0|max:9999',
            'tipo'             => 'nullable|in:salgada,doce',
            'adicionais'       => 'nullable|array|max:30',
            'adicionais.*'     => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'ingredientes'     => 'nullable|array|max:20',
            'ingredientes.*'   => 'string|max:30|distinct',
            'tamanhos'         => 'nullable|array|max:6',
            'tamanhos.*.nome'  => 'required|string|max:30|distinct',
            'tamanhos.*.preco' => 'required|numeric|min:0|max:9999',
            'cor'       => 'nullable|integer|between:0,16777215',
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
