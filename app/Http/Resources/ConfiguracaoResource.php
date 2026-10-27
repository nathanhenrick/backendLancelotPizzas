<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguracaoResource extends JsonResource
{
    private const TEXTOS = [
        'razao_social', 'cnpj', 'descricao', 'email', 'telefone', 'instagram', 'facebook',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf',
        'area_entrega', 'taxa_entrega', 'tempo_entrega', 'tempo_retirada', 'formas_pagamento',
    ];

    public function toArray(Request $request): array
    {
        $dados = [
            'nome'        => $this->nome,
            'whatsapp'    => $this->whatsapp ?? '',
            'regra_preco' => $this->regra_preco,
        ];
        foreach (self::TEXTOS as $campo) {
            $dados[$campo] = $this->{$campo} ?? '';
        }
        $dados['horarios'] = $this->horarios ?? [];
        $dados['atualizado_em'] = $this->updated_at?->format('d/m/Y H:i');

        return $dados;
    }
}
