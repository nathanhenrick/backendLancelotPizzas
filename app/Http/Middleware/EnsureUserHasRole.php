<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$papeis): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! in_array($user->papel, $papeis, true)) {
            return new JsonResponse([
                'status' => 'Error',
                'message' => 'Você não tem permissão para esta ação.',
                'errors' => [],
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
