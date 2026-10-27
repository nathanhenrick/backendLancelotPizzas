<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->status !== 'ATIVO') {
            return new JsonResponse([
                'status' => 'Error',
                'message' => 'Acesso restrito a administradores.',
                'errors' => [],
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
