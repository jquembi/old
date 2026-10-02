<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiGateway\SyncController;
use App\Http\Controllers\ApiGateway\TenantController;
use App\Http\Controllers\ApiGateway\HealthController;

/*
|--------------------------------------------------------------------------
| API Gateway Routes
|--------------------------------------------------------------------------
|
| Rotas para comunicação entre nodes e núcleo central.
| Estas rotas são protegidas por autenticação via API tokens.
|
*/

Route::middleware(['auth:sanctum', 'set.tenant:validate'])->group(function () {
    
    // Health Check
    Route::get('/health', [HealthController::class, 'check'])->name('api.gateway.health');
    
    // Sync Operations
    Route::prefix('sync')->group(function () {
        Route::post('/push', [SyncController::class, 'pushToCentral'])->name('api.gateway.sync.push');
        Route::post('/pull', [SyncController::class, 'pullFromCentral'])->name('api.gateway.sync.pull');
        Route::get('/status/{syncLog}', [SyncController::class, 'status'])->name('api.gateway.sync.status');
        Route::get('/history', [SyncController::class, 'history'])->name('api.gateway.sync.history');
    });
    
    // Tenant Management
    Route::prefix('tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('api.gateway.tenants.index');
        Route::get('/{tenant}', [TenantController::class, 'show'])->name('api.gateway.tenants.show');
        Route::post('/{tenant}/online', [TenantController::class, 'markOnline'])->name('api.gateway.tenants.online');
        Route::post('/{tenant}/offline', [TenantController::class, 'markOffline'])->name('api.gateway.tenants.offline');
        Route::get('/{tenant}/indicators', [TenantController::class, 'indicators'])->name('api.gateway.tenants.indicators');
    });
    
    // Offline Operations
    Route::prefix('offline')->group(function () {
        Route::post('/queue', [SyncController::class, 'queueOfflineOperation'])->name('api.gateway.offline.queue');
        Route::get('/pending', [SyncController::class, 'pendingOfflineOperations'])->name('api.gateway.offline.pending');
        Route::post('/process', [SyncController::class, 'processOfflineOperations'])->name('api.gateway.offline.process');
    });
    
    // National Indicators (Central only)
    Route::middleware(['tenant.type:central'])->prefix('indicators')->group(function () {
        Route::get('/', [TenantController::class, 'nationalIndicators'])->name('api.gateway.indicators.index');
        Route::post('/', [TenantController::class, 'storeIndicator'])->name('api.gateway.indicators.store');
        Route::get('/{indicator}', [TenantController::class, 'showIndicator'])->name('api.gateway.indicators.show');
    });
});
