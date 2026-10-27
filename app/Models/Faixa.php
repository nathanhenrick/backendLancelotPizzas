<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faixa extends Model
{
    protected $table = 'faixas';

    protected $fillable = [
        'codigo',
        'nome',
        'preco_p',
        'preco_m',
        'preco_g',
    ];

    protected function casts(): array
    {
        return [
            'preco_p' => 'float',
            'preco_m' => 'float',
            'preco_g' => 'float',
        ];
    }

    public function sabores()
    {
        return $this->hasMany(Sabor::class, 'faixa_id');
    }
}
