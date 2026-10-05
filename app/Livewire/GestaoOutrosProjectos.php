<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OutroProjecto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoOutrosProjectos extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public string $filtroTipo = '';
    public string $filtroEstado = '';

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        if (!$user || (!$user->temPermissao('outros_projectos.ver') && !$user->temPermissao('requalificacao.ver') && !$user->temPermissao('instrumentos.ver'))) {
            abort(403, 'Não tem permissão para visualizar outros projectos.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->reset(['search', 'filtroTipo', 'filtroEstado']);
        $this->resetPage();
    }

    public function eliminar(int $id): void
    {
        $user = Auth::user();
        if (!$user || (!$user->temPermissao('outros_projectos.eliminar') && !$user->temPermissao('instrumentos.eliminar'))) {
            $this->dispatch('notificar', [
                'tipo' => 'error',
                'mensagem' => 'Não tem permissão para eliminar este projecto.',
            ]);
            return;
        }

        $projecto = OutroProjecto::where('municipio_id', $this->municipio->id)->findOrFail($id);
        $titulo = $projecto->titulo;
        $projecto->delete();

        session()->flash('status', "Projecto '{$titulo}' enviado para a lixeira com sucesso.");
    }

    public function render()
    {
        $user = Auth::user();
        $podeCriar = $user ? ($user->temPermissao('outros_projectos.criar') || $user->temPermissao('instrumentos.criar')) : false;
        $podeEditar = $user ? ($user->temPermissao('outros_projectos.editar') || $user->temPermissao('instrumentos.editar')) : false;
        $podeEliminar = $user ? ($user->temPermissao('outros_projectos.eliminar') || $user->temPermissao('instrumentos.eliminar')) : false;

        $baseQuery = OutroProjecto::query()->where('municipio_id', $this->municipio->id);

        $kpis = [
            'total' => (clone $baseQuery)->count(),
            'requalificacao' => (clone $baseQuery)->where('tipo', 'requalificacao')->count(),
            'infraestrutura' => (clone $baseQuery)->where('tipo', 'infraestrutura')->count(),
            'social' => (clone $baseQuery)->where('tipo', 'social')->count(),
            'outro' => (clone $baseQuery)->where('tipo', 'outro')->count(),
            'em_curso' => (clone $baseQuery)->where('estado', 'em_curso')->count(),
            'concluido' => (clone $baseQuery)->where('estado', 'concluido')->count(),
            'planificado' => (clone $baseQuery)->where('estado', 'planificado')->count(),
            'suspenso' => (clone $baseQuery)->where('estado', 'suspenso')->count(),
            'orcamento_total' => (clone $baseQuery)->sum('orcamento'),
        ];

        $query = (clone $baseQuery)->with(['criadoPor'])->latest();

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'like', $term)
                  ->orWhere('descricao', 'like', $term)
                  ->orWhere('entidade_responsavel', 'like', $term)
                  ->orWhere('fase', 'like', $term);
            });
        }

        if ($this->filtroTipo) {
            $query->where('tipo', $this->filtroTipo);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        $projectos = $query->paginate(12);

        return view('livewire.gestao-outros-projectos', [
            'projectos' => $projectos,
            'kpis' => $kpis,
            'podeCriar' => $podeCriar,
            'podeEditar' => $podeEditar,
            'podeEliminar' => $podeEliminar,
        ]);
    }
}
