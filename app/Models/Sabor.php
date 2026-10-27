<?php

namespace App\Models;

use App\Support\VisivelNoCardapio;
use Illuminate\Database\Eloquent\Model;

class Sabor extends Model
{
    use VisivelNoCardapio;

    public const TIPOS = ['salgada', 'doce'];

    protected $table = 'sabores';

    protected $fillable = [
        'faixa_id',
        'nome',
        'tipo',
        'descricao',
        'ingredientes',
        'adicionais',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ingredientes' => 'array',
            'adicionais'   => 'array',
            'ativo'        => 'boolean',
        ];
    }

    public function faixa()
    {
        return $this->belongsTo(Faixa::class);
    }
}
