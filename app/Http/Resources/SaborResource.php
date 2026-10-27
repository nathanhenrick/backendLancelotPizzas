<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaborResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nome'         => $this->nome,
            'tipo'         => $this->tipo ?? 'salgada',
            'faixa'        => $this->whenLoaded('faixa', fn() => $this->faixa->codigo),
            'descricao'    => $this->descricao ?? '',
            'ingredientes' => $this->ingredientes ?? [],
            'adicionais'   => array_map('intval', $this->adicionais ?? []),
            'ativo'        => $this->ativo,
        ];
    }
}
