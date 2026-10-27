<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sabor\StoreSaborRequest;
use App\Http\Requests\Sabor\UpdateSaborRequest;
use App\Http\Resources\SaborResource;
use App\Services\SaborService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class SaborController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(SaborService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $sabores = $this->service->getAll();
            return $this->success('Sabores listados', SaborResource::collection($sabores));
        } catch (Exception $e) {
            return $this->error('Erro ao listar sabores', 500, $e->getMessage());
        }
    }

    public function store(StoreSaborRequest $request): JsonResponse
    {
        try {
            $sabor = $this->service->store($request->validated());
            return $this->success('Sabor criado', new SaborResource($sabor), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar sabor', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $sabor = $this->service->findById($id);
            return $this->success('Sabor encontrado', new SaborResource($sabor));
        } catch (Exception $e) {
            return $this->error('Sabor não encontrado', 404, $e->getMessage());
        }
    }

    public function update(UpdateSaborRequest $request, int $id): JsonResponse
    {
        try {
            $sabor = $this->service->update($id, $request->validated());
            return $this->success('Sabor atualizado', new SaborResource($sabor));
        } catch (ModelNotFoundException $e) {
            return $this->error('Sabor não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar sabor', 500, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->success('Sabor excluído');
        } catch (ModelNotFoundException $e) {
            return $this->error('Sabor não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir sabor', 500, $e->getMessage());
        }
    }
}
