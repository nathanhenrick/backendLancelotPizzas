<?php

namespace App\Http\Requests\Configuracao;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracaoRequest extends FormRequest
{
    private const DIGITOS = ['whatsapp', 'cnpj', 'telefone', 'cep'];

    private const TEXTOS = [
        'razao_social', 'descricao', 'email', 'instagram', 'facebook', 'logradouro', 'numero',
        'complemento', 'bairro', 'cidade', 'uf', 'area_entrega', 'taxa_entrega', 'tempo_entrega',
        'tempo_retirada', 'formas_pagamento',
    ];

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        foreach (self::DIGITOS as $campo) {
            if ($this->has($campo)) {
                $this->merge([$campo => preg_replace('/\D/', '', (string) $this->input($campo)) ?: null]);
            }
        }
        foreach (self::TEXTOS as $campo) {
            if ($this->has($campo)) {
                $valor = trim((string) $this->input($campo));
                $this->merge([$campo => $campo === 'uf' ? (strtoupper($valor) ?: null) : ($valor ?: null)]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'nome'                 => 'sometimes|required|string|max:80',
            'whatsapp'             => 'sometimes|nullable|digits_between:12,13',
            'regra_preco'          => 'sometimes|in:maior,media',
            'razao_social'         => 'sometimes|nullable|string|max:120',
            'cnpj'                 => 'sometimes|nullable|digits:14',
            'descricao'            => 'sometimes|nullable|string|max:1000',
            'email'                => 'sometimes|nullable|email|max:120',
            'telefone'             => 'sometimes|nullable|digits_between:10,11',
            'instagram'            => 'sometimes|nullable|string|max:60',
            'facebook'             => 'sometimes|nullable|string|max:100',
            'cep'                  => 'sometimes|nullable|digits:8',
            'logradouro'           => 'sometimes|nullable|string|max:120',
            'numero'               => 'sometimes|nullable|string|max:15',
            'complemento'          => 'sometimes|nullable|string|max:60',
            'bairro'               => 'sometimes|nullable|string|max:80',
            'cidade'               => 'sometimes|nullable|string|max:80',
            'uf'                   => 'sometimes|nullable|alpha|size:2',
            'horarios'             => 'sometimes|nullable|array|size:7',
            'horarios.*.dia'       => 'required|string|max:20',
            'horarios.*.fechado'   => 'required|boolean',
            'horarios.*.abre'      => 'nullable|date_format:H:i',
            'horarios.*.fecha'     => 'nullable|date_format:H:i',
            'area_entrega'         => 'sometimes|nullable|string|max:500',
            'taxa_entrega'         => 'sometimes|nullable|string|max:120',
            'tempo_entrega'        => 'sometimes|nullable|string|max:60',
            'tempo_retirada'       => 'sometimes|nullable|string|max:60',
            'formas_pagamento'     => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'           => 'O nome da pizzaria é obrigatório.',
            'whatsapp.digits_between' => 'Informe código do país, DDD e número (12 ou 13 dígitos).',
            'regra_preco.in'          => 'A regra de preço deve ser "maior" ou "media".',
            'cnpj.digits'             => 'O CNPJ deve ter 14 dígitos.',
            'telefone.digits_between' => 'Informe o DDD e o telefone completo.',
            'cep.digits'              => 'O CEP deve ter 8 dígitos.',
            'uf.size'                 => 'Informe a sigla do estado (2 letras).',
            'email.email'             => 'Informe um e-mail válido.',
        ];
    }
}
