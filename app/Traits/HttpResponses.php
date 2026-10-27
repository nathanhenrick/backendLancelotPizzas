<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait HttpResponses
{
    protected function success(string $message, mixed $data = [], int $code = 200, ?array $meta = null): JsonResponse
    {
        return response()->json([
            'status'  => 'Success',
            'message' => $message,
            'data'    => $data,
        ] + ($meta !== null ? ['meta' => $meta] : []), $code);
    }

    protected function error(string $message, int $code, mixed $errors = []): JsonResponse
    {
        if ($code >= 500) {
            if ($errors !== [] && $errors !== '' && $errors !== null) {
                Log::error($message, ['detalhe' => $errors]);
            }
            $errors = [];
        }

        return response()->json([
            'status'  => 'Error',
            'message' => $message,
            'errors'  => $errors,
            'data'    => []
        ], $code);
    }
}
