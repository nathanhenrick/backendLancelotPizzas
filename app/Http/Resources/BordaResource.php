<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BordaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'nome'  => $this->nome,
            'p'     => (float) $this->preco_p,
            'm'     => (float) $this->preco_m,
            'g'     => (float) $this->preco_g,
            'cor'   => $this->cor,
            'ativo' => $this->ativo,
        ];
    }
}
