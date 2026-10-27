<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait VisivelNoCardapio
{
    public function scopeNoCardapio(Builder $query): Builder
    {
        return self::donoEstaLogado() ? $query : $query->where($this->qualifyColumn('ativo'), true);
    }

    private static function donoEstaLogado(): bool
    {
        $user = auth('sanctum')->user();

        return $user instanceof User
            && $user->status === 'ATIVO'
            && $user->ehDono();
    }
}
