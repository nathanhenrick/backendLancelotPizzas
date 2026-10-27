<?php

namespace App\Services;

use App\Models\Configuracao;
use Illuminate\Support\Facades\DB;

class ConfiguracaoService
{
    public function getSettings()
    {
        return Configuracao::firstOrCreate([], ['nome' => 'Pizzaria', 'regra_preco' => 'maior']);
    }

    public function update(array $data)
    {
        return DB::transaction(function () use ($data) {
            $configuracao = $this->getSettings();
            $configuracao->update($data);
            return $configuracao;
        });
    }
}
