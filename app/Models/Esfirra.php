<?php

namespace App\Models;

use App\Support\VisivelNoCardapio;
use Illuminate\Database\Eloquent\Model;

class Esfirra extends Model
{
    use VisivelNoCardapio;

    protected $table = 'esfirras';

    protected $fillable = ['nome', 'tipo', 'descricao', 'preco', 'tamanhos', 'ingredientes', 'adicionais', 'cor', 'ativo'];

    public function tamanhosComPreco(): array
    {
        $lista = collect($this->tamanhos ?? [])
            ->map(fn ($t) => ['nome' => (string) $t['nome'], 'preco' => (float) $t['preco']])
            ->values()
            ->all();

        return $lista ?: [['nome' => 'Unidade', 'preco' => (float) $this->preco]];
    }

    protected function casts(): array
    {
        return [
            'preco' => 'float',
            'cor'   => 'integer',
            'tamanhos' => 'array',
            'ingredientes' => 'array',
            'adicionais' => 'array',
            'ativo' => 'boolean',
        ];
    }
}
