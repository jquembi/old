<?php

namespace App\Http\Livewire\Nacional;

use App\Models\Auditoria;
use App\Models\NationalAuditLog;
use App\Models\SystemError;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Auditoria nacional: "eventos" consolida os registos do núcleo; "logs" mostra
 * os registos técnicos (requisições auditadas e erros do sistema).
 */
class AuditoriaNacional extends Component
{
    use WithPagination;

    public string $vista = 'eventos';

    public string $search = '';

    public string $acao = '';

    public ?int $tenantId = null;

    public ?string $de = null;

    public ?string $ate = null;

    public ?int $detalheId = null;

    public function mount(string $vista = 'eventos'): void
    {
        $this->vista = $vista === 'logs' ? 'logs' : 'eventos';
    }

    public function updated($property): void
    {
        if ($property === 'tenantId') {
            $this->tenantId = $this->tenantId ?: null;
        }
        $this->resetPage();
    }

    public function ver(int $id): void
    {
        $this->detalheId = $id;
    }

    public function fechar(): void
    {
        $this->detalheId = null;
    }

    public function limparFiltros(): void
    {
        $this->reset(['search', 'acao', 'tenantId', 'de', 'ate']);
        $this->resetPage();
    }

    private function eventos()
    {
        return NationalAuditLog::query()->with(['tenant:id,code,name', 'user:id,name'])
            ->when($this->acao, fn ($q) => $q->where('action', $this->acao))
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->when($this->de, fn ($q) => $q->whereDate('created_at', '>=', $this->de))
            ->when($this->ate, fn ($q) => $q->whereDate('created_at', '<=', $this->ate))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('description', 'like', "%{$this->search}%")
                ->orWhere('entity_type', 'like', "%{$this->search}%")
                ->orWhere('ip_address', 'like', "%{$this->search}%")))
            ->recent();
    }

    private function logs()
    {
        return Auditoria::query()->with('user:id,name')
            ->when($this->acao, fn ($q) => $q->where('accao', $this->acao))
            ->when($this->de, fn ($q) => $q->whereDate('created_at', '>=', $this->de))
            ->when($this->ate, fn ($q) => $q->whereDate('created_at', '<=', $this->ate))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('url', 'like', "%{$this->search}%")
                ->orWhere('route_name', 'like', "%{$this->search}%")
                ->orWhere('modelo', 'like', "%{$this->search}%")
                ->orWhere('ip_address', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $eventos = $this->vista === 'eventos';
        $registos = ($eventos ? $this->eventos() : $this->logs())->paginate(15);

        $detalhe = null;
        if ($this->detalheId) {
            $detalhe = $eventos
                ? NationalAuditLog::with(['tenant:id,code,name', 'user:id,name'])->find($this->detalheId)
                : Auditoria::with('user:id,name')->find($this->detalheId);
        }

        $inicio = now()->subDay();

        return view('livewire.nacional.auditoria-nacional', [
            'eventos' => $eventos,
            'registos' => $registos,
            'detalhe' => $detalhe,
            'tenants' => Tenant::orderBy('name')->pluck('name', 'id'),
            'acoes' => $eventos
                ? NationalAuditLog::query()->distinct()->orderBy('action')->pluck('action')
                : Auditoria::query()->distinct()->orderBy('accao')->pluck('accao'),
            'resumo' => [
                'total' => $eventos ? NationalAuditLog::count() : Auditoria::count(),
                'hoje' => $eventos ? NationalAuditLog::where('created_at', '>=', $inicio)->count() : Auditoria::where('created_at', '>=', $inicio)->count(),
                'erros' => SystemError::whereNull('resolved_at')->count(),
            ],
        ]);
    }
}
