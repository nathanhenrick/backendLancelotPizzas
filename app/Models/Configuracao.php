<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = [
        'nome',
        'whatsapp',
        'regra_preco',
        'razao_social',
        'cnpj',
        'descricao',
        'email',
        'telefone',
        'instagram',
        'facebook',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'horarios',
        'area_entrega',
        'taxa_entrega',
        'tempo_entrega',
        'tempo_retirada',
        'formas_pagamento',
    ];

    protected $casts = ['horarios' => 'array'];
}
