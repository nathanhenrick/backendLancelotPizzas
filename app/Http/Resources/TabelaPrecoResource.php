<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TabelaPrecoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tamanhos' => TamanhoResource::collection($this->resource['tamanhos']),
            'faixas'   => $this->resource['faixas']->mapWithKeys(fn($faixa) => [
                $faixa->codigo => [
                    'nome' => $faixa->nome,
                    'p'    => (float) $faixa->preco_p,
                    'm'    => (float) $faixa->preco_m,
                    'g'    => (float) $faixa->preco_g,
                ],
            ]),
        ];
    }
}
