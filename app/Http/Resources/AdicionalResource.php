<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdicionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'nome'  => $this->nome,
            'preco' => (float) $this->preco,
            'ativo' => $this->ativo,
        ];
    }
}
