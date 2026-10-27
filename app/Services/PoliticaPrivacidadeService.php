<?php

namespace App\Services;

use App\Models\PoliticaPrivacidade;
use Illuminate\Support\Facades\DB;

class PoliticaPrivacidadeService
{
    public function obter(): ?PoliticaPrivacidade
    {
        return PoliticaPrivacidade::query()->orderBy('id')->first();
    }

    public function salvar(array $dados): PoliticaPrivacidade
    {
        return DB::transaction(function () use ($dados) {
            $politica = $this->obter() ?? new PoliticaPrivacidade;
            $politica->fill($dados)->save();

            return $politica;
        });
    }
}
