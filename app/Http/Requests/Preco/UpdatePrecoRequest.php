<?php

namespace App\Http\Requests\Preco;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrecoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tamanhos'               => 'required|array|min:1|max:3',
            'tamanhos.*.id'          => 'required|distinct|in:p,m,g',
            'tamanhos.*.nome'        => 'required|string|max:40',
            'tamanhos.*.info'        => 'nullable|string|max:80',
            'tamanhos.*.max_sabores' => 'required|integer|between:1,4',

            'faixas'                 => 'required|array:trad,esp,prem',
            'faixas.*.nome'          => 'required|string|max:40',
            'faixas.*.p'             => 'required|numeric|min:0|max:9999',
            'faixas.*.m'             => 'required|numeric|min:0|max:9999',
            'faixas.*.g'             => 'required|numeric|min:0|max:9999',
        ];
    }

    public function messages(): array
    {
        return [
            'required'                       => 'O campo :attribute é obrigatório.',
            'faixas.array'                   => 'Faixa de preço inválida.',
            'tamanhos.*.id.in'               => 'Tamanho inválido.',
            'tamanhos.*.max_sabores.between' => 'O máximo de sabores vai de 1 a 4.',
            'numeric'                        => 'O preço deve ser um número.',
            'min'                            => 'O preço não pode ser negativo.',
        ];
    }
}
