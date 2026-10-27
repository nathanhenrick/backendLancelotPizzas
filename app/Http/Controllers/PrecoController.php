<?php

namespace App\Http\Controllers;

use App\Http\Requests\Preco\UpdatePrecoRequest;
use App\Http\Resources\TabelaPrecoResource;
use App\Services\PrecoService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;

class PrecoController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(PrecoService $service)
    {
        $this->service = $service;
    }

    public function show(): JsonResponse
    {
        try {
            $tabela = $this->service->getTabela();
            return $this->success('Tamanhos e preços', new TabelaPrecoResource($tabela));
        } catch (Exception $e) {
            return $this->error('Erro ao buscar tamanhos e preços', 500, $e->getMessage());
        }
    }

    public function update(UpdatePrecoRequest $request): JsonResponse
    {
        try {
            $tabela = $this->service->update($request->validated());
            return $this->success('Tamanhos e preços atualizados', new TabelaPrecoResource($tabela));
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar tamanhos e preços', 500, $e->getMessage());
        }
    }
}
