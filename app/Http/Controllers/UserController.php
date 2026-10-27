<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\HttpResponses;
use DomainException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HttpResponses;

    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $users = $this->service->getAll(
                max((int) $request->query('pagina', 1), 1),
                min(max((int) $request->query('por_pagina', 100), 1), 200),
            );

            return $this->success('Usuários listados', UserResource::collection($users->getCollection()), 200, [
                'pagina'        => $users->currentPage(),
                'por_pagina'    => $users->perPage(),
                'total'         => $users->total(),
                'ultima_pagina' => $users->lastPage(),
            ]);
        } catch (Exception $e) {
            return $this->error('Erro ao listar usuários', 500, $e->getMessage());
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->service->store($request->validated());
            return $this->success('Usuário criado', new UserResource($user), 201);
        } catch (Exception $e) {
            return $this->error('Erro ao criar usuário', 500, $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->service->findById($id);
            return $this->success('Usuário encontrado', new UserResource($user));
        } catch (Exception $e) {
            return $this->error('Usuário não encontrado', 404, $e->getMessage());
        }
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->service->update($id, $request->validated(), $request->user());
            return $this->success('Usuário atualizado', new UserResource($user));
        } catch (DomainException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->error('Usuário não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao atualizar usuário', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->delete($id, $request->user());
            return $this->success('Usuário excluído');
        } catch (DomainException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->error('Usuário não encontrado', 404);
        } catch (Exception $e) {
            return $this->error('Erro ao excluir usuário', 500, $e->getMessage());
        }
    }
}
