<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamanho extends Model
{
    protected $table = 'tamanhos';

    protected $fillable = [
        'codigo',
        'nome',
        'info',
        'max_sabores',
        'escala',
    ];

    protected function casts(): array
    {
        return [
            'max_sabores' => 'integer',
            'escala'      => 'float',
        ];
    }

    public function colunaPreco(): string
    {
        return 'preco_' . $this->codigo;
    }
}
