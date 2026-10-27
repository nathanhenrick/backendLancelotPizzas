<?php

namespace App\Http\Controllers;

use App\Http\Requests\PoliticaPrivacidade\UpdatePoliticaPrivacidadeRequest;
use App\Models\PoliticaPrivacidade;
use App\Services\PoliticaPrivacidadeService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;

class PoliticaPrivacidadeController extends Controller
{
    use HttpResponses;

    public function __construct(protected PoliticaPrivacidadeService $service) {}

    public function mostrar(): JsonResponse
    {
        try {
            return $this->success('Política de privacidade', $this->dados($this->service->obter()))
                ->header('Cache-Control', 'public, max-age=60');
        } catch (Exception $e) {
            return $this->error('Erro ao buscar a política de privacidade', 500, $e->getMessage());
        }
    }

    public function salvar(UpdatePoliticaPrivacidadeRequest $request): JsonResponse
    {
        try {
            return $this->success('Política de privacidade salva', $this->dados($this->service->salvar($request->validated())));
        } catch (Exception $e) {
            return $this->error('Erro ao salvar a política de privacidade', 500, $e->getMessage());
        }
    }

    private function dados(?PoliticaPrivacidade $politica): array
    {
        return [
            'texto'         => $politica?->texto ?? '',
            'responsavel'   => $politica?->responsavel ?? '',
            'email'         => $politica?->email ?? '',
            'atualizado_em' => $politica?->updated_at?->format('d/m/Y'),
        ];
    }
}
