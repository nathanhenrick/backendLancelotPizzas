<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TamanhoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->codigo,
            'nome'        => $this->nome,
            'info'        => $this->info ?? '',
            'max_sabores' => $this->max_sabores,
            'escala'      => (float) $this->escala,
        ];
    }
}
