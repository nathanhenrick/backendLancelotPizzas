<?php

namespace App\Http\Controllers;

use App\Http\Requests\Borda\StoreBordaRequest;
use App\Http\Requests\Borda\UpdateBordaRequest;
use App\Http\Resources\BordaResource;
use App\Services\BordaService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class BordaController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(BordaService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $bordas = $this->service->getAll();
            return $this->success('Bordas listadas', BordaResource::collection($bordas));
        } catch (Exception $e) {
            return $this->error('Erro ao listar bordas', 500, $e->getMessage());
        }
    }

    public function store(StoreBordaRequest $request): JsonResponse
    {
        try {
            $borda = $this->service->store($request->validated());
            return $this->success('Borda criada', new BordaResource($borda), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar borda', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $borda = $this->service->findById($id);
            return $this->success('Borda encontrada', new BordaResource($borda));
        } catch (Exception $e) {
            return $this->error('Borda não encontrada', 404, $e->getMessage());
        }
    }

    public function update(UpdateBordaRequest $request, int $id): JsonResponse
    {
        try {
            $borda = $this->service->update($id, $request->validated());
            return $this->success('Borda atualizada', new BordaResource($borda));
        } catch (ModelNotFoundException $e) {
            return $this->error('Borda não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar borda', 500, $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->success('Borda excluída');
        } catch (ModelNotFoundException $e) {
            return $this->error('Borda não encontrada', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir borda', 500, $e->getMessage());
        }
    }
}
