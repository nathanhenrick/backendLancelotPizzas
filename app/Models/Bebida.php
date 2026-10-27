<?php

namespace App\Models;

use App\Support\VisivelNoCardapio;
use Illuminate\Database\Eloquent\Model;

class Bebida extends Model
{
    use VisivelNoCardapio;

    public const MODELOS = ['garrafa_2l', 'garrafa_1l', 'garrafa_500', 'lata', 'jarra', 'taca', 'copo', 'long_neck', 'cerveja_471', 'cerveja_1l'];

    protected $table = 'bebidas';

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'modelo',
        'cor',
        'logo',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'float',
            'cor'   => 'integer',
            'ativo' => 'boolean',
        ];
    }
}
