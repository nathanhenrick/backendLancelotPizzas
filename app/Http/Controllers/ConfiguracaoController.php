<?php

namespace App\Http\Controllers;

use App\Http\Requests\Configuracao\UpdateConfiguracaoRequest;
use App\Http\Resources\ConfiguracaoResource;
use App\Services\ConfiguracaoService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;

class ConfiguracaoController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(ConfiguracaoService $service)
    {
        $this->service = $service;
    }

    public function show(): JsonResponse
    {
        try {
            $configuracao = $this->service->getSettings();
            return $this->success('Configurações da pizzaria', new ConfiguracaoResource($configuracao));
        } catch (Exception $e) {
            return $this->error('Erro ao buscar configurações', 500, $e->getMessage());
        }
    }

    public function update(UpdateConfiguracaoRequest $request): JsonResponse
    {
        try {
            $configuracao = $this->service->update($request->validated());
            return $this->success('Configurações atualizadas', new ConfiguracaoResource($configuracao));
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar configurações', 500, $e->getMessage());
        }
    }
}
