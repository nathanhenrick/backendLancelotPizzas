<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bebida\StoreBebidaRequest;
use App\Http\Requests\Bebida\UpdateBebidaRequest;
use App\Http\Resources\BebidaResource;
use App\Services\BebidaService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class BebidaController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(BebidaService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $bebidas = $this->service->getAll();
            return $this->success('Bebidas listadas', BebidaResource::collection($bebidas));
        } catch (Exception $e) {
            return $this->error('Erro ao listar bebidas', 500, $e->getMessage());
        }
    }

    public function store(StoreBebidaRequest $request): JsonResponse
    {
        try {
            $bebida = $this->service->store($request->validated());
            return $this->success('Bebida criada', new BebidaResource($bebida), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar bebida', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $bebida = $this->service->findById($id);
            return $this->success('Bebida encontrada', new BebidaResource($bebida));
        } catch (Exception $e) {
            return $this->error('Bebida não encontrada', 404, $e->getMessage());
        }
    }

    public function update(UpdateBebidaRequest $request, int $id): JsonResponse
    {
        try {
            $bebida = $this->service->update($id, $request->validated());
            return $this->success('Bebida atualizada', new BebidaResource($bebida));
        } catch (ModelNotFoundException $e) {
            return $this->error('Bebida não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar bebida', 500, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->success('Bebida excluída');
        } catch (ModelNotFoundException $e) {
            return $this->error('Bebida não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir bebida', 500, $e->getMessage());
        }
    }
}
