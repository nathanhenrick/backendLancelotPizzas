<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EsfirraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nome'      => $this->nome,
            'descricao' => $this->descricao ?? '',
            'preco'     => (float) $this->preco,
            'tipo'      => $this->tipo ?? 'salgada',
            'adicionais' => array_map('intval', $this->adicionais ?? []),
            'ingredientes' => $this->ingredientes ?? [],
            'tamanhos'  => $this->tamanhosComPreco(),
            'cor'       => $this->cor,
            'ativo'     => $this->ativo,
        ];
    }
}
