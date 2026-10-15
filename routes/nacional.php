<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetTenant;
use App\Http\Livewire\Nacional\DashboardNacional;
use App\Http\Livewire\Nacional\AdministracaoNacional;
use App\Http\Livewire\Nacional\AuditoriaNacional;
use App\Http\Livewire\Nacional\Indicadores;
use App\Http\Livewire\Nacional\NodeDetalhe;
use App\Http\Livewire\Nacional\Parametros;
use App\Http\Livewire\Nacional\Relatorios;
use App\Http\Livewire\Nacional\SupervisaoNodes;

/*
|--------------------------------------------------------------------------
| Rotas do Núcleo Central Nacional
|--------------------------------------------------------------------------
|
| Rotas exclusivas para o Núcleo Central Nacional (governação e supervisão).
| Estas rotas só são acessíveis quando o tenant atual é do tipo 'central'.
|
*/

Route::middleware(['web', 'auth', 'verified', 'set.tenant:validate', 'geo.context', 'geo.angola', 'tenant.type:central', 'perspetiva'])->group(function () {
    
    // Dashboard Nacional
    Route::get('/nacional', DashboardNacional::class)->name('nacional.dashboard');

    // Indicadores Nacionais
    Route::prefix('nacional/indicadores')->group(function () {
        Route::get('/', Indicadores::class)->name('nacional.indicadores');
        Route::get('/processos', Indicadores::class)->defaults('categoria', 'processos')->name('nacional.indicadores.processos');
        Route::get('/terras', Indicadores::class)->defaults('categoria', 'terras')->name('nacional.indicadores.terras');
        Route::get('/financeiro', Indicadores::class)->defaults('categoria', 'financeiro')->name('nacional.indicadores.financeiro');
    });

    // Supervisão de Nodes
    Route::prefix('nacional/nodes')->group(function () {
        Route::get('/', SupervisaoNodes::class)->name('nacional.nodes');
        Route::get('/{tenant}', NodeDetalhe::class)->whereNumber('tenant')->name('nacional.node.detalhe');
    });

    // Relatórios Nacionais
    Route::prefix('nacional/relatorios')->group(function () {
        Route::get('/', Relatorios::class)->name('nacional.relatorios');
        Route::get('/gerar', fn () => redirect()->route('nacional.relatorios'))->name('nacional.relatorios.gerar');
    });

    // Administração Nacional
    Route::prefix('nacional/administracao')->group(function () {
        Route::get('/parametros', Parametros::class)->name('nacional.parametros');
        Route::get('/usuarios-nacionais', AdministracaoNacional::class)->name('nacional.usuarios-nacionais');
        Route::get('/regras-globais', Parametros::class)->defaults('modo', 'regras')->name('nacional.regras-globais');
    });

    // Auditoria Nacional
    Route::prefix('nacional/auditoria')->group(function () {
        Route::get('/', AuditoriaNacional::class)->name('nacional.auditoria');
        Route::get('/logs', AuditoriaNacional::class)->defaults('vista', 'logs')->name('nacional.auditoria.logs');
    });
});