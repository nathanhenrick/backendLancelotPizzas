<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    use HttpResponses;

    public function index(): JsonResponse
    {
        $hoje = Carbon::today();
        $inicioRanking = Carbon::now()->subDays(30)->startOfDay();

        $validos = Pedido::where('status', '!=', 'cancelado');

        $pedidosHoje = (clone $validos)->where('created_at', '>=', $hoje);
        $totalValidos = (clone $validos)->count();

        $ultimosPedidos = Pedido::with('itens')
            ->latest('id')
            ->limit(6)
            ->get();

        $contagem = [];
        (clone $validos)
            ->where('created_at', '>=', $inicioRanking)
            ->select(['id', 'sabores'])
            ->lazyById(500)
            ->each(function (Pedido $pedido) use (&$contagem) {
                foreach (array_unique($pedido->sabores ?? []) as $nome) {
                    $contagem[$nome] = ($contagem[$nome] ?? 0) + 1;
                }
            });
        arsort($contagem);
        $saboresMaisPedidos = collect($contagem)
            ->take(5)
            ->map(fn($qtd, $nome) => ['nome' => $nome, 'qtd' => $qtd])
            ->values();

        return $this->success('Resumo do painel', [
            'pedidos_hoje'         => (clone $pedidosHoje)->count(),
            'vendas_hoje'          => (float) (clone $pedidosHoje)->sum('valor_total'),
            'aguardando'           => Pedido::whereIn('status', ['novo', 'preparo'])->count(),
            'ticket_medio'         => $totalValidos ? round((float) (clone $validos)->sum('valor_total') / $totalValidos, 2) : 0,
            'ultimos_pedidos'      => PedidoResource::collection($ultimosPedidos),
            'sabores_mais_pedidos' => $saboresMaisPedidos,
        ]);
    }
}
