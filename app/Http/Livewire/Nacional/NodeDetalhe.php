<?php

namespace App\Http\Livewire\Nacional;

use App\Models\NationalAuditLog;
use App\Models\SyncLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Nacional\GestaoNodesService;
use Livewire\Component;
use Livewire\WithPagination;

class NodeDetalhe extends Component
{
    use WithPagination;

    public Tenant $node;

    public string $estadoSync = '';

    public function mount(int|string $tenant): void
    {
        $this->node = Tenant::findOrFail($tenant);
    }

    public function updatedEstadoSync(): void
    {
        $this->resetPage();
    }

    public function alternarAtivo(GestaoNodesService $nodes): void
    {
        $this->autorizar();
        $resultado = $nodes->alternarAtivo($this->node);
        $this->node->refresh();
        $this->dispatch('toast', message: $resultado['mensagem'], type: $resultado['tipo']);
    }

    public function sincronizar(GestaoNodesService $nodes): void
    {
        $this->autorizar();
        $resultado = $nodes->pedirSincronizacao($this->node);
        $this->dispatch('toast', message: $resultado['mensagem'], type: $resultado['tipo']);
    }

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->isSuperAdminNacional() || auth()->user()?->isAdminNacional(), 403);
    }

    public function render()
    {
        $logs = SyncLog::with('targetTenant')
            ->where(fn ($q) => $q->where('source_tenant_id', $this->node->id)->orWhere('target_tenant_id', $this->node->id))
            ->when($this->estadoSync, fn ($q) => $q->where('status', $this->estadoSync))
            ->recent()
            ->paginate(10);

        $base = SyncLog::where('source_tenant_id', $this->node->id);
        $total = (clone $base)->count();

        $utilizadores = $this->node->isProvincial()
            ? User::whereHas('provincia', fn ($q) => $q->where('codigo', $this->node->code))->count()
            : null;

        return view('livewire.nacional.node-detalhe', [
            'tenant' => $this->node,
            'logs' => $logs,
            'totalSyncs' => $total,
            'sucessos' => (clone $base)->success()->count(),
            'falhas' => (clone $base)->failed()->count(),
            'registos' => (int) (clone $base)->sum('records_count'),
            'utilizadores' => $utilizadores,
            'auditoria' => NationalAuditLog::where('tenant_id', $this->node->id)->recent()->limit(8)->get(),
            'podeGerir' => auth()->user()?->isSuperAdminNacional() || auth()->user()?->isAdminNacional(),
        ]);
    }
}
