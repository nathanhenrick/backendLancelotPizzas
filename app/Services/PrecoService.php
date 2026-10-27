<?php

namespace App\Services;

use App\Models\Faixa;
use App\Models\Tamanho;
use Illuminate\Support\Facades\DB;

class PrecoService
{
    public function getTabela()
    {
        return [
            'tamanhos' => Tamanho::orderBy('escala')->get(),
            'faixas'   => Faixa::orderBy('id')->get(),
        ];
    }

    public function update(array $data)
    {
        return DB::transaction(function () use ($data) {
            foreach ($data['tamanhos'] as $tamanho) {
                Tamanho::where('codigo', $tamanho['id'])->update([
                    'nome'        => $tamanho['nome'],
                    'info'        => $tamanho['info'] ?? null,
                    'max_sabores' => $tamanho['max_sabores'],
                ]);
            }

            foreach ($data['faixas'] as $codigo => $faixa) {
                Faixa::where('codigo', $codigo)->update([
                    'nome'    => $faixa['nome'],
                    'preco_p' => $faixa['p'],
                    'preco_m' => $faixa['m'],
                    'preco_g' => $faixa['g'],
                ]);
            }

            return $this->getTabela();
        });
    }
}
