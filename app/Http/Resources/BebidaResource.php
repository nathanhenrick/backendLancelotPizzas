<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BebidaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'nome'   => $this->nome,
            'descricao' => $this->descricao ?? '',
            'preco'  => (float) $this->preco,
            'modelo' => $this->modelo,
            'cor'    => $this->cor,
            'logo'   => $this->logo,
            'ativo'  => $this->ativo,
        ];
    }
}
