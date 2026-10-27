<?php

namespace App\Http\Requests\Borda;

use Illuminate\Foundation\Http\FormRequest;

class StoreBordaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'  => 'required|string|max:80',
            'p'     => 'required|numeric|min:0|max:9999',
            'm'     => 'required|numeric|min:0|max:9999',
            'g'     => 'required|numeric|min:0|max:9999',
            'cor'   => 'nullable|integer|between:0,16777215',
            'ativo' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'required'    => 'O campo :attribute é obrigatório.',
            'numeric'     => 'O preço deve ser um número.',
            'min'         => 'O preço não pode ser negativo.',
            'cor.between' => 'A cor do recheio é inválida.',
        ];
    }
}
