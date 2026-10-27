<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    public const TRANSICOES = [
        'novo'      => ['preparo', 'saiu', 'concluido', 'cancelado'],
        'preparo'   => ['saiu', 'concluido', 'cancelado'],
        'saiu'      => ['concluido', 'cancelado'],
        'concluido' => [],
        'cancelado' => [],
    ];

    protected $table = 'pedidos';

    protected $fillable = [
        'cliente',
        'modo',
        'status',
        'tamanho',
        'sabores',
        'valor_pizza',
        'valor_borda',
        'valor_bebidas',
        'valor_esfirras',
        'valor_adicionais',
        'valor_total',
    ];

    protected function casts(): array
    {
        return [
            'sabores'       => 'array',
            'valor_pizza'   => 'float',
            'valor_borda'   => 'float',
            'valor_bebidas' => 'float',
            'valor_esfirras' => 'float',
            'valor_adicionais' => 'float',
            'valor_total'   => 'float',
        ];
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
