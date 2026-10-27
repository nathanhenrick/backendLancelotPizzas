<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use HttpResponses;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());
            return $this->success('Login realizado com sucesso', [
                'user'  => new UserResource($data['user']),
                'token' => $data['token'],
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return $this->success('Token revogado com sucesso');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success('Usuário autenticado', new UserResource($request->user()));
    }
}
