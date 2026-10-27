<?php

namespace App\Models;

use App\Support\VisivelNoCardapio;
use Illuminate\Database\Eloquent\Model;

class Borda extends Model
{
    use VisivelNoCardapio;

    protected $table = 'bordas';

    protected $fillable = [
        'nome',
        'preco_p',
        'preco_m',
        'preco_g',
        'cor',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_p' => 'float',
            'preco_m' => 'float',
            'preco_g' => 'float',
            'cor'     => 'integer',
            'ativo'   => 'boolean',
        ];
    }
}
