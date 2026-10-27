<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'cliente'   => $this->cliente ?? '',
            'modo'      => $this->modo,
            'status'    => $this->status,
            'tamanho'   => $this->tamanho,
            'sabores'   => $this->sabores ?? [],
            'linhas'    => $this->whenLoaded('itens', fn() => $this->itens->map(fn($item) => [
                'titulo'   => $item->titulo,
                'detalhes' => $item->detalhes ?? [],
                'preco'    => (float) $item->valor,
            ])),
            'total'     => (float) $this->valor_total,
            'criado_em' => $this->created_at?->toIso8601String(),
        ];
    }
}
