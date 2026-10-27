<?php

namespace App\Http\Requests\Borda;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBordaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'  => 'sometimes|string|max:80',
            'p'     => 'sometimes|numeric|min:0|max:9999',
            'm'     => 'sometimes|numeric|min:0|max:9999',
            'g'     => 'sometimes|numeric|min:0|max:9999',
            'cor'   => 'sometimes|nullable|integer|between:0,16777215',
            'ativo' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'numeric'     => 'O preço deve ser um número.',
            'min'         => 'O preço não pode ser negativo.',
            'cor.between' => 'A cor do recheio é inválida.',
        ];
    }
}
