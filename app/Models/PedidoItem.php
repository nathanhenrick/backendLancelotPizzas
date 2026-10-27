<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedido_itens';

    protected $fillable = [
        'pedido_id',
        'titulo',
        'detalhes',
        'valor',
    ];

    protected function casts(): array
    {
        return [
            'detalhes' => 'array',
            'valor'    => 'float',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
