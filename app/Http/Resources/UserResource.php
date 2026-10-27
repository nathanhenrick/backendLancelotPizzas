<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nome'      => $this->nome,
            'email'     => $this->email,
            'status'    => $this->status,
            'papel'     => $this->papel,
            'criado_em' => $this->created_at?->format('d/m/Y'),
        ];
    }
}
