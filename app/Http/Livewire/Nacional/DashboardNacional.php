<?php

namespace App\Http\Livewire\Nacional;

use App\Models\Tenant;
use App\Models\NationalIndicator;
use App\Models\SyncLog;
use App\Models\NationalAuditLog;
use Livewire\Component;

class DashboardNacional extends Component
{
    public $totalTenants = 0;
    public $activeTenants = 0;
    public $onlineTenants = 0;
    public $totalSyncs = 0;
    public $successfulSyncs = 0;
    public $failedSyncs = 0;
    public $totalAudits = 0;
    public $recentSyncs = [];
    public $recentAudits = [];
    public $tenantStatus = [];
    public $indicators = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        // Estatísticas de Tenants
        $this->totalTenants = Tenant::count();
        $this->activeTenants = Tenant::where('is_active', true)->count();
        $this->onlineTenants = Tenant::online()->count();

        // Estatísticas de Sincronização
        $this->totalSyncs = SyncLog::count();
        $this->successfulSyncs = SyncLog::where('status', SyncLog::STATUS_SUCCESS)->count();
        $this->failedSyncs = SyncLog::where('status', SyncLog::STATUS_FAILED)->count();

        // Estatísticas de Auditoria
        $this->totalAudits = NationalAuditLog::count();

        // Sincronizações Recentes
        $this->recentSyncs = SyncLog::with(['sourceTenant', 'targetTenant'])
            ->recent()
            ->limit(10)
            ->get();

        // Auditorias Recentes
        $this->recentAudits = NationalAuditLog::recent()->limit(10)->get();

        // Status dos Tenants
        $this->tenantStatus = Tenant::with(['syncLogs' => fn ($query) => $query
            ->orderByDesc('completed_at')])
            ->get()
            ->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'code' => $tenant->code,
                'name' => $tenant->name,
                'type' => $tenant->type,
                'is_active' => $tenant->is_active,
                'is_online' => $tenant->isOnline(),
                'sync_status' => $tenant->sync_status,
                'last_sync' => $tenant->syncLogs->first()?->completed_at,
            ];
        });

        // Indicadores Nacionais
        $this->indicators = $this->getNationalIndicators();
    }

    private function getNationalIndicators(): array
    {
        return [
            'processos_fundiarios' => [
                'total' => $this->indicatorTotal('processos_fundiarios'),
                'aprovados' => $this->indicatorTotal('processos_aprovados'),
                'pendentes' => $this->indicatorTotal('processos_pendentes'),
            ],
            'terrenos_ociosos' => [
                'total' => $this->indicatorTotal('terrenos_ociosos'),
                'identificados' => $this->indicatorTotal('terrenos_identificados'),
                'recuperados' => $this->indicatorTotal('terrenos_recuperados'),
            ],
            'instrumentos_urbanisticos' => [
                'total' => $this->indicatorTotal('instrumentos_urbanisticos'),
                'aprovados' => $this->indicatorTotal('instrumentos_aprovados'),
                'em_elaboracao' => $this->indicatorTotal('instrumentos_em_elaboracao'),
            ],
            'operacoes_urbanisticas' => [
                'total' => $this->indicatorTotal('operacoes_urbanisticas'),
                'concluidas' => $this->indicatorTotal('operacoes_concluidas'),
                'em_curso' => $this->indicatorTotal('operacoes_em_curso'),
            ],
        ];
    }

    private function indicatorTotal(string $type): float
    {
        return NationalIndicator::byType($type)
            ->get(['value'])
            ->sum(fn (NationalIndicator $indicator) => (float) data_get($indicator->value, 'total', 0));
    }

    public function render()
    {
        return view('livewire.nacional.dashboard-nacional')
            ->layout('layouts.app');
    }
}
