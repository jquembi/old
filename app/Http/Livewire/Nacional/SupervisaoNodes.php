<?php

namespace App\Http\Livewire\Nacional;

use App\Models\SyncLog;
use App\Models\Tenant;
use App\Services\AuditoriaCentralizadaService;
use App\Services\Nacional\GestaoNodesService;
use Livewire\Component;
use Livewire\WithPagination;

class SupervisaoNodes extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tipo = '';

    public string $estado = '';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function alternarAtivo(int $id, GestaoNodesService $nodes): void
    {
        $this->autorizar();
        $mensagem = $nodes->alternarAtivo(Tenant::findOrFail($id));
        $this->dispatch('toast', message: $mensagem['mensagem'], type: $mensagem['tipo']);
    }

    public function sincronizar(int $id, GestaoNodesService $nodes): void
    {
        $this->autorizar();
        $mensagem = $nodes->pedirSincronizacao(Tenant::findOrFail($id));
        $this->dispatch('toast', message: $mensagem['mensagem'], type: $mensagem['tipo']);
    }

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->isSuperAdminNacional() || auth()->user()?->isAdminNacional(), 403);
    }

    public function render()
    {
        $limite = (int) \App\Models\ParametroNacional::obter('sync.limite_offline_minutos', 60);

        $nodes = Tenant::query()
            ->withCount(['syncLogs as falhas_count' => fn ($q) => $q->where('status', SyncLog::STATUS_FAILED)])
            ->withMax('syncLogs as ultima_sync', 'completed_at')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->tipo, fn ($q) => $q->where('type', $this->tipo))
            ->when($this->estado === 'ativo', fn ($q) => $q->where('is_active', true))
            ->when($this->estado === 'inativo', fn ($q) => $q->where('is_active', false))
            ->when(in_array($this->estado, ['online', 'offline', 'syncing'], true), fn ($q) => $q->where('sync_status', $this->estado))
            ->orderBy('type')
            ->orderBy('name')
            ->paginate((int) \App\Models\ParametroNacional::obter('plataforma.registos_por_pagina', 15) ?: 15);

        return view('livewire.nacional.supervisao-nodes', [
            'nodes' => $nodes,
            'limiteOffline' => $limite,
            'resumo' => [
                'total' => Tenant::count(),
                'ativos' => Tenant::where('is_active', true)->count(),
                'online' => Tenant::online()->where('is_active', true)->count(),
                'falhas' => SyncLog::failed()->where('started_at', '>=', now()->subDay())->count(),
            ],
            'podeGerir' => auth()->user()?->isSuperAdminNacional() || auth()->user()?->isAdminNacional(),
        ]);
    }
}
