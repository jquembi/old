<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetTenant;
use App\Http\Livewire\Nacional\DashboardNacional;
use App\Http\Livewire\Nacional\AdministracaoNacional;

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
        Route::get('/', fn () => view('nacional.modulo', [
            'title' => 'Indicadores Nacionais',
            'description' => 'Acompanhe os indicadores consolidados da plataforma nacional.',
        ]))->name('nacional.indicadores');
        
        Route::get('/processos', fn () => view('nacional.modulo', [
            'title' => 'Indicadores de Processos',
            'description' => 'Consulte a evolução dos processos fundiários em todo o país.',
        ]))->name('nacional.indicadores.processos');
        
        Route::get('/terras', fn () => view('nacional.modulo', [
            'title' => 'Indicadores de Terras',
            'description' => 'Acompanhe os dados territoriais consolidados por província.',
        ]))->name('nacional.indicadores.terras');
        
        Route::get('/financeiro', fn () => view('nacional.modulo', [
            'title' => 'Indicadores Financeiros',
            'description' => 'Consulte os indicadores financeiros disponíveis na plataforma.',
        ]))->name('nacional.indicadores.financeiro');
    });

    // Supervisão de Nodes
    Route::prefix('nacional/nodes')->group(function () {
        Route::get('/', fn () => view('nacional.modulo', [
            'title' => 'Supervisão de Nodes',
            'description' => 'Monitorize a disponibilidade e o estado de sincronização dos nodes.',
        ]))->name('nacional.nodes');
        
        Route::get('/{tenant}', fn ($tenant) => view('nacional.modulo', [
            'title' => 'Detalhe do Node',
            'description' => "Informação operacional do node #{$tenant}.",
        ]))->name('nacional.node.detalhe');
        
        Route::post('/{tenant}/ativar', function ($tenantId) {
            // Lógica de ativação de node
            return back()->with('success', 'Node ativado com sucesso');
        })->name('nacional.node.ativar');
        
        Route::post('/{tenant}/desativar', function ($tenantId) {
            // Lógica de desativação de node
            return back()->with('success', 'Node desativado com sucesso');
        })->name('nacional.node.desativar');
        
        Route::post('/{tenant}/sync', function ($tenantId) {
            // Lógica de sincronização forçada
            return back()->with('success', 'Sincronização iniciada');
        })->name('nacional.node.sync');
    });

    // Relatórios Nacionais
    Route::prefix('nacional/relatorios')->group(function () {
        Route::get('/', fn () => view('nacional.modulo', [
            'title' => 'Relatórios Nacionais',
            'description' => 'Centralize a consulta e a preparação de relatórios institucionais.',
        ]))->name('nacional.relatorios');
        
        Route::get('/gerar', fn () => redirect()
            ->route('nacional.relatorios')
            ->with('info', 'A geração de relatórios estará disponível quando o modelo for configurado.')
        )->name('nacional.relatorios.gerar');
    });

    // Administração Nacional
    Route::prefix('nacional/administracao')->group(function () {
        Route::get('/parametros', fn () => view('nacional.modulo', [
            'title' => 'Parâmetros Nacionais',
            'description' => 'Consulte os parâmetros globais que orientam a operação da plataforma.',
        ]))->name('nacional.parametros');
        
        Route::get('/usuarios-nacionais', AdministracaoNacional::class)->name('nacional.usuarios-nacionais');
        
        Route::get('/regras-globais', fn () => view('nacional.modulo', [
            'title' => 'Regras Globais',
            'description' => 'Consulte as regras que orientam a governação nacional da plataforma.',
        ]))->name('nacional.regras-globais');
    });

    // Auditoria Nacional
    Route::prefix('nacional/auditoria')->group(function () {
        Route::get('/', fn () => view('nacional.modulo', [
            'title' => 'Auditoria Nacional',
            'description' => 'Aceda aos eventos de auditoria consolidados da plataforma.',
        ]))->name('nacional.auditoria');
        
        Route::get('/logs', fn () => view('nacional.modulo', [
            'title' => 'Registos de Auditoria',
            'description' => 'Consulte os registos técnicos e operacionais da auditoria nacional.',
        ]))->name('nacional.auditoria.logs');
    });
});
