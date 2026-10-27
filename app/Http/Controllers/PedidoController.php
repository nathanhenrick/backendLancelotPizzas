<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Http\Requests\Pedido\UpdatePedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Services\PedidoService;
use App\Traits\HttpResponses;
use DomainException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(PedidoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $pedidos = $this->service->getAllAdmin(
                $request->only('status'),
                max((int) $request->query('pagina', 1), 1),
                min(max((int) $request->query('por_pagina', 200), 1), 500),
            );

            return $this->success('Pedidos listados (Admin)', PedidoResource::collection($pedidos->getCollection()), 200, [
                'pagina'        => $pedidos->currentPage(),
                'por_pagina'    => $pedidos->perPage(),
                'total'         => $pedidos->total(),
                'ultima_pagina' => $pedidos->lastPage(),
            ]);
        } catch (Exception $e) {
            return $this->error('Erro ao listar pedidos', 500, $e->getMessage());
        }
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        try {
            $pedido = $this->service->store($request->validated());
            return $this->success('Pedido gerado com sucesso', new PedidoResource($pedido), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao processar pedido', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $pedido = $this->service->findById($id);
            return $this->success('Detalhes do Pedido', new PedidoResource($pedido));
        } catch (Exception $e) {
            return $this->error('Pedido não encontrado', 404, $e->getMessage());
        }
    }

    public function update(UpdatePedidoRequest $request, int $id): JsonResponse
    {
        try {
            $pedido = $this->service->update($id, $request->validated());
            return $this->success('Pedido atualizado com sucesso', new PedidoResource($pedido));
        } catch (DomainException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->error('Pedido não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar pedido', 500, $e->getMessage());
        }
    }
}
