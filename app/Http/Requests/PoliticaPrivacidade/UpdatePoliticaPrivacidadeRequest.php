<?php

namespace App\Http\Requests\PoliticaPrivacidade;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePoliticaPrivacidadeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        foreach (['texto', 'responsavel', 'email'] as $campo) {
            if ($this->has($campo) && is_string($this->input($campo)) && trim($this->input($campo)) === '') {
                $this->merge([$campo => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'texto'       => 'sometimes|nullable|string|max:40000',
            'responsavel' => 'sometimes|nullable|string|max:120',
            'email'       => 'sometimes|nullable|email|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Informe um e-mail válido para o contato de privacidade.',
            'max'         => 'O campo :attribute excede o tamanho máximo.',
        ];
    }
}
