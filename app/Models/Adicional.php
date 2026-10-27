<?php

namespace App\Models;

use App\Support\VisivelNoCardapio;
use Illuminate\Database\Eloquent\Model;

class Adicional extends Model
{
    use VisivelNoCardapio;

    protected $table = 'adicionais';

    protected $fillable = ['nome', 'preco', 'ativo'];

    protected function casts(): array
    {
        return [
            'preco' => 'float',
            'ativo' => 'boolean',
        ];
    }
}
