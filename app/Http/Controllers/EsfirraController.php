<?php

namespace App\Http\Controllers;

use App\Http\Requests\Esfirra\StoreEsfirraRequest;
use App\Http\Requests\Esfirra\UpdateEsfirraRequest;
use App\Http\Resources\EsfirraResource;
use App\Services\EsfirraService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class EsfirraController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(EsfirraService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $esfirras = $this->service->getAll();
            return $this->success('Esfirras listadas', EsfirraResource::collection($esfirras));
        } catch (Exception $e) {
            return $this->error('Erro ao listar esfirras', 500, $e->getMessage());
        }
    }

    public function store(StoreEsfirraRequest $request): JsonResponse
    {
        try {
            $esfirra = $this->service->store($request->validated());
            return $this->success('Esfirra criada', new EsfirraResource($esfirra), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar esfirra', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $esfirra = $this->service->findById($id);
            return $this->success('Esfirra encontrada', new EsfirraResource($esfirra));
        } catch (Exception $e) {
            return $this->error('Esfirra não encontrada', 404, $e->getMessage());
        }
    }

    public function update(UpdateEsfirraRequest $request, int $id): JsonResponse
    {
        try {
            $esfirra = $this->service->update($id, $request->validated());
            return $this->success('Esfirra atualizada', new EsfirraResource($esfirra));
        } catch (ModelNotFoundException $e) {
            return $this->error('Esfirra não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar esfirra', 500, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->success('Esfirra excluída');
        } catch (ModelNotFoundException $e) {
            return $this->error('Esfirra não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir esfirra', 500, $e->getMessage());
        }
    }
}
