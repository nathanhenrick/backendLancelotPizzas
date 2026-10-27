<?php

namespace App\Http\Controllers;

use App\Http\Requests\Adicional\StoreAdicionalRequest;
use App\Http\Requests\Adicional\UpdateAdicionalRequest;
use App\Http\Resources\AdicionalResource;
use App\Services\AdicionalService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class AdicionalController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(AdicionalService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $adicionais = $this->service->getAll();
            return $this->success('Adicionais listados', AdicionalResource::collection($adicionais));
        } catch (Exception $e) {
            return $this->error('Erro ao listar adicionais', 500, $e->getMessage());
        }
    }

    public function store(StoreAdicionalRequest $request): JsonResponse
    {
        try {
            $adicional = $this->service->store($request->validated());
            return $this->success('Adicional criado', new AdicionalResource($adicional), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar adicional', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $adicional = $this->service->findById($id);
            return $this->success('Adicional encontrado', new AdicionalResource($adicional));
        } catch (Exception $e) {
            return $this->error('Adicional não encontrado', 404, $e->getMessage());
        }
    }

    public function update(UpdateAdicionalRequest $request, int $id): JsonResponse
    {
        try {
            $adicional = $this->service->update($id, $request->validated());
            return $this->success('Adicional atualizado', new AdicionalResource($adicional));
        } catch (ModelNotFoundException $e) {
            return $this->error('Adicional não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar adicional', 500, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->success('Adicional excluído');
        } catch (ModelNotFoundException $e) {
            return $this->error('Adicional não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir adicional', 500, $e->getMessage());
        }
    }
}
