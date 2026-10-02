<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConsultaPublicaController;
use App\Http\Controllers\Api\V1\CoordinateController;
use App\Http\Controllers\Api\V1\CroquiController;
use App\Http\Controllers\Api\V1\InstrumentoController;
use App\Http\Controllers\Api\V1\LoteamentoController;
use App\Http\Controllers\Api\V1\MunicipioController;
use App\Http\Controllers\Api\V1\OperacaoController;
use App\Http\Controllers\Api\V1\OperationFileController;
use App\Http\Controllers\Api\V1\ParecerController;
use App\Http\Controllers\Api\V1\SonaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — G.TERRAS · HUÍLA
|--------------------------------------------------------------------------
| Autenticação via Sanctum (token Bearer para Electron / sessão para web).
| Prefixo automático: /api  (definido em bootstrap/app.php)
| Rate limiting: 60 req/min por utilizador (configurado em AppServiceProvider)
|--------------------------------------------------------------------------
*/

// ─── Auth (público) ─────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Health check (público, usado pelo Electron para verificar conectividade) ───
Route::get('v1/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));
Route::get('v1/consulta', ConsultaPublicaController::class)
    ->middleware('throttle:30,1')
    ->name('api.consulta.publica');

// ─── Rotas protegidas ───────────────────────────────────
Route::middleware(['auth:sanctum', 'set.tenant:validate', 'geo.context', 'geo.angola'])->prefix('v1')->group(function () {

    // ─── Auth ────────────────────────────────────────────
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // ─── Municípios ──────────────────────────────────────
    Route::get('municipios',              [MunicipioController::class, 'index']);
    Route::get('municipios/{municipio}',  [MunicipioController::class, 'show']);
    Route::get('municipios/{municipio}/stats', [MunicipioController::class, 'stats']);

    // ─── Operações Urbanísticas ──────────────────────────
    Route::get('operacoes/{operacao}/coordenadas', [OperacaoController::class, 'coordenadas']);
    Route::apiResource('operacoes', OperacaoController::class)
        ->parameters(['operacoes' => 'operacao']);


    // ─── Croquis (dentro de uma operação) ────────────────
    Route::get('operacoes/{operacao}/croquis',          [CroquiController::class, 'index']);
    Route::post('operacoes/{operacao}/croquis',         [CroquiController::class, 'store']);

    Route::get('croquis/{croqui}',    [CroquiController::class, 'show']);
    Route::put('croquis/{croqui}',    [CroquiController::class, 'update']);
    Route::delete('croquis/{croqui}', [CroquiController::class, 'destroy']);

    // ─── Ficheiros técnicos ──────────────────────────────
    Route::get('operacoes/{operacao}/ficheiros',  [OperationFileController::class, 'index']);
    Route::post('operacoes/{operacao}/ficheiros', [OperationFileController::class, 'store']);

    Route::get('ficheiros/{operationFile}',          [OperationFileController::class, 'show']);
    Route::get('ficheiros/{operationFile}/download',  [OperationFileController::class, 'download'])
        ->name('api.operation-files.download');
    Route::delete('ficheiros/{operationFile}',       [OperationFileController::class, 'destroy']);

    // ─── Pareceres ───────────────────────────────────────
    Route::apiResource('pareceres', ParecerController::class)
        ->parameters(['pareceres' => 'parecer']);

    // ─── Instrumentos ────────────────────────────────────
    Route::apiResource('instrumentos', InstrumentoController::class);

    // ─── Loteamentos ─────────────────────────────────────
    Route::apiResource('loteamentos', LoteamentoController::class);

    // ─── Projectos SONA ──────────────────────────────────
    Route::apiResource('sona', SonaController::class)->parameters(['sona' => 'projectoSona']);

    // ─── Conversão de coordenadas ────────────────────────
    Route::post('coordinates/convert',      [CoordinateController::class, 'convert']);
    Route::get('coordinates/default-zone',  [CoordinateController::class, 'defaultZone']);
});
