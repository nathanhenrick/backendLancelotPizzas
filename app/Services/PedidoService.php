<?php

namespace App\Services;

use App\Models\Adicional;
use App\Models\Bebida;
use App\Models\Borda;
use App\Models\Esfirra;
use App\Models\Pedido;
use App\Models\Sabor;
use App\Models\Tamanho;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    private const FRACOES = ['', 'Inteira', '1/2', '1/3', '1/4'];

    public function __construct(private ConfiguracaoService $configuracao) {}

    public function getAllAdmin(array $filtros = [], int $pagina = 1, int $porPagina = 200): LengthAwarePaginator
    {
        return Pedido::with('itens')
            ->when($filtros['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate($porPagina, ['*'], 'pagina', $pagina);
    }

    public function findById(int $id)
    {
        return Pedido::with('itens')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $ids = $data['sabores'] ?? [];
            $tamanho = $ids ? Tamanho::where('codigo', $data['tamanho'])->firstOrFail() : null;
            $coluna = $tamanho?->colunaPreco();

            $cadastrados = Sabor::with('faixa')->whereIn('id', $ids)->get()->keyBy('id');
            $sabores = collect($ids)->map(fn($id) => $cadastrados[$id]);

            $precos = $sabores->map(fn(Sabor $sabor) => (float) $sabor->faixa->{$coluna});
            $valorPizza = $sabores->isEmpty() ? 0.0 : ($this->configuracao->getSettings()->regra_preco === 'media'
                ? round($precos->avg(), 2)
                : (float) $precos->max());

            $ajustes = $data['ajustes'] ?? [];
            ksort($ajustes);
            $ajustes = array_values($ajustes);
            $catalogo = Adicional::whereIn('id', collect($ajustes)->pluck('adicionais')->flatten()->filter()->unique())->get()->keyBy('id');
            $valorAdicionais = 0.0;
            $detalhesAdicionais = [];

            $prefixo = $sabores->count() > 1 ? self::FRACOES[$sabores->count()] . ' ' : '';
            $partes = $sabores->values()->map(function (Sabor $sabor, int $i) use ($ajustes, $catalogo, $prefixo, &$valorAdicionais, &$detalhesAdicionais) {
                $ajuste = $ajustes[$i] ?? [];
                $notas = [];
                foreach ($ajuste['removidos'] ?? [] as $removido) {
                    $notas[] = 'sem ' . mb_strtolower($removido);
                }
                foreach ($ajuste['adicionais'] ?? [] as $id) {
                    $extra = $catalogo[$id];
                    $notas[] = '+ ' . $extra->nome;
                    $valorAdicionais += (float) $extra->preco;
                    $detalhesAdicionais[] = $extra->nome . ($prefixo ? " ({$prefixo}{$sabor->nome})" : '');
                }
                if (trim($ajuste['observacao'] ?? '') !== '') {
                    $notas[] = 'obs: ' . trim($ajuste['observacao']);
                }

                return $prefixo . $sabor->nome . ($notas ? ' (' . implode('; ', $notas) . ')' : '');
            })->all();

            $itens = $sabores->isEmpty() ? [] : [[
                'titulo'   => "Pizza {$tamanho->nome}",
                'detalhes' => $partes,
                'valor'    => $valorPizza,
            ]];
            if ($valorAdicionais > 0) {
                $itens[] = ['titulo' => 'Adicionais', 'detalhes' => $detalhesAdicionais, 'valor' => round($valorAdicionais, 2)];
            }

            $valorBorda = 0.0;
            if ($sabores->isNotEmpty() && !empty($data['borda'])) {
                $borda = Borda::findOrFail($data['borda']);
                $valorBorda = (float) $borda->{$coluna};
                if ($borda->cor !== null || $valorBorda > 0) {
                    $itens[] = ['titulo' => 'Borda', 'detalhes' => [$borda->nome], 'valor' => $valorBorda];
                }
            }

            $valorBebidas = 0.0;
            $escolhidas = collect($data['bebidas'] ?? []);
            if ($escolhidas->isNotEmpty()) {
                $bebidas = Bebida::whereIn('id', $escolhidas->pluck('id'))->orderBy('id')->get();
                foreach ($bebidas as $bebida) {
                    $quantidade = (int) $escolhidas->firstWhere('id', $bebida->id)['quantidade'];
                    $valor = round($quantidade * $bebida->preco, 2);
                    $valorBebidas += $valor;
                    $itens[] = ['titulo' => "{$quantidade}× {$bebida->nome}", 'detalhes' => [], 'valor' => $valor];
                }
            }

            $valorEsfirras = 0.0;
            $pedidas = collect($data['esfirras'] ?? []);
            if ($pedidas->isNotEmpty()) {
                $cadastradas = Esfirra::whereIn('id', $pedidas->pluck('id'))->get()->keyBy('id');
                foreach ($pedidas->sortBy('id') as $pedida) {
                    $esfirra = $cadastradas[$pedida['id']];
                    $tamanhos = collect($esfirra->tamanhosComPreco());
                    $escolhido = $tamanhos->firstWhere('nome', $pedida['tamanho'] ?? null) ?? $tamanhos->first();
                    $quantidade = (int) $pedida['quantidade'];

                    $ajuste = $pedida['ajuste'] ?? [];
                    $extras = Adicional::whereIn('id', $ajuste['adicionais'] ?? [])->get();
                    $notas = [];
                    foreach ($ajuste['removidos'] ?? [] as $removido) {
                        $notas[] = 'sem ' . mb_strtolower($removido);
                    }
                    foreach ($extras as $extra) {
                        $notas[] = '+ ' . $extra->nome;
                    }
                    if (trim($ajuste['observacao'] ?? '') !== '') {
                        $notas[] = 'obs: ' . trim($ajuste['observacao']);
                    }

                    $valor = round($quantidade * ($escolhido['preco'] + (float) $extras->sum('preco')), 2);
                    $valorEsfirras += $valor;
                    $sufixo = $tamanhos->count() > 1 ? " ({$escolhido['nome']})" : '';
                    $itens[] = ['titulo' => "{$quantidade}× {$esfirra->nome}{$sufixo}", 'detalhes' => $notas ? [implode('; ', $notas)] : [], 'valor' => $valor];
                }
            }

            $pedido = Pedido::create([
                'cliente'       => trim($data['cliente'] ?? '') ?: null,
                'modo'          => $data['modo'],
                'status'        => 'novo',
                'tamanho'       => $tamanho?->nome ?? '—',
                'sabores'       => $sabores->pluck('nome')->all(),
                'valor_pizza'   => $valorPizza,
                'valor_borda'   => $valorBorda,
                'valor_bebidas' => $valorBebidas,
                'valor_esfirras' => $valorEsfirras,
                'valor_adicionais' => round($valorAdicionais, 2),
                'valor_total'   => round($valorPizza + $valorBorda + $valorBebidas + $valorEsfirras + $valorAdicionais, 2),
            ]);

            $pedido->itens()->createMany($itens);
            $pedido->load('itens');

            return $pedido;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $pedido = Pedido::lockForUpdate()->findOrFail($id);

            if (isset($data['status']) && $data['status'] !== $pedido->status
                && ! in_array($data['status'], Pedido::TRANSICOES[$pedido->status] ?? [], true)) {
                throw new DomainException("Um pedido {$pedido->status} não pode passar para {$data['status']}.");
            }

            $pedido->update($data);
            $pedido->load('itens');
            return $pedido;
        });
    }
}
