<?php

namespace App\Http\Requests\Esfirra;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEsfirraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'      => 'sometimes|string|max:80',
            'descricao' => 'sometimes|nullable|string|max:160',
            'preco' => 'sometimes|numeric|min:0|max:9999',
            'tipo'             => 'sometimes|in:salgada,doce',
            'adicionais'       => 'sometimes|nullable|array|max:30',
            'adicionais.*'     => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'ingredientes'     => 'sometimes|nullable|array|max:20',
            'ingredientes.*'   => 'string|max:30|distinct',
            'tamanhos'         => 'sometimes|nullable|array|max:6',
            'tamanhos.*.nome'  => 'required|string|max:30|distinct',
            'tamanhos.*.preco' => 'required|numeric|min:0|max:9999',
            'cor'       => 'sometimes|nullable|integer|between:0,16777215',
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
