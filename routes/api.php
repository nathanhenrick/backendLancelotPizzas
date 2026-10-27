<?php

use App\Http\Controllers\AdicionalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BebidaController;
use App\Http\Controllers\BordaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EsfirraController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PoliticaPrivacidadeController;
use App\Http\Controllers\PrecoController;
use App\Http\Controllers\SaborController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    });

    Route::get('/politica-privacidade', [PoliticaPrivacidadeController::class, 'mostrar']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::get('/admin/pedidos', [PedidoController::class, 'index']);
        Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->whereNumber('id');
        Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->whereNumber('id');

        Route::middleware('papel:dono')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::get('/users/{id}', [UserController::class, 'show'])->whereNumber('id');
            Route::put('/users/{id}', [UserController::class, 'update'])->whereNumber('id');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])->whereNumber('id');

            Route::get('/dashboard', [DashboardController::class, 'index']);

            Route::put('/configuracoes', [ConfiguracaoController::class, 'update']);

            Route::put('/politica-privacidade', [PoliticaPrivacidadeController::class, 'salvar']);

            Route::put('/precos', [PrecoController::class, 'update']);

            Route::post('/sabores', [SaborController::class, 'store']);
            Route::put('/sabores/{id}', [SaborController::class, 'update'])->whereNumber('id');
            Route::delete('/sabores/{id}', [SaborController::class, 'destroy'])->whereNumber('id');

            Route::post('/bordas', [BordaController::class, 'store']);
            Route::put('/bordas/{id}', [BordaController::class, 'update'])->whereNumber('id');
            Route::delete('/bordas/{id}', [BordaController::class, 'destroy'])->whereNumber('id');

            Route::post('/bebidas', [BebidaController::class, 'store']);
            Route::put('/bebidas/{id}', [BebidaController::class, 'update'])->whereNumber('id');
            Route::delete('/bebidas/{id}', [BebidaController::class, 'destroy'])->whereNumber('id');

            Route::post('/esfirras', [EsfirraController::class, 'store']);
            Route::put('/esfirras/{id}', [EsfirraController::class, 'update'])->whereNumber('id');
            Route::delete('/esfirras/{id}', [EsfirraController::class, 'destroy'])->whereNumber('id');

            Route::post('/adicionais', [AdicionalController::class, 'store']);
            Route::put('/adicionais/{id}', [AdicionalController::class, 'update'])->whereNumber('id');
            Route::delete('/adicionais/{id}', [AdicionalController::class, 'destroy'])->whereNumber('id');
        });
    });

    Route::group([], function () {
        Route::get('/configuracoes', [ConfiguracaoController::class, 'show']);

        Route::get('/precos', [PrecoController::class, 'show']);

        Route::get('/sabores', [SaborController::class, 'index']);
        Route::get('/sabores/{id}', [SaborController::class, 'show'])->whereNumber('id');

        Route::get('/bordas', [BordaController::class, 'index']);
        Route::get('/bordas/{id}', [BordaController::class, 'show'])->whereNumber('id');

        Route::get('/bebidas', [BebidaController::class, 'index']);
        Route::get('/bebidas/{id}', [BebidaController::class, 'show'])->whereNumber('id');

        Route::get('/esfirras', [EsfirraController::class, 'index']);
        Route::get('/esfirras/{id}', [EsfirraController::class, 'show'])->whereNumber('id');

        Route::get('/adicionais', [AdicionalController::class, 'index']);
        Route::get('/adicionais/{id}', [AdicionalController::class, 'show'])->whereNumber('id');

        Route::post('/pedidos', [PedidoController::class, 'store'])->middleware('throttle:pedidos');
    });
});
