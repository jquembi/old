<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\ProjectoSona;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoProjectoSona extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public string $filtroTipo = '';
    public string $filtroEstado = '';
    public string $filtroFase = '';

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        if (!$user || (!$user->temPermissao('sona.ver') && !$user->temPermissao('requalificacao.ver'))) {
            abort(403, 'Não tem permissão para visualizar os projectos SONA.');
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

    public function updatingFiltroFase(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->reset(['search', 'filtroTipo', 'filtroEstado', 'filtroFase']);
        $this->resetPage();
    }

    public function eliminar(int $id): void
    {
        $user = Auth::user();
        if (!$user || (!$user->temPermissao('sona.eliminar') && !$user->temPermissao('instrumentos.eliminar'))) {
            $this->dispatch('notificar', [
                'tipo' => 'error',
                'mensagem' => 'Não tem permissão para eliminar este projecto SONA.',
            ]);
            return;
        }

        $projecto = ProjectoSona::where('municipio_id', $this->municipio->id)->findOrFail($id);
        $titulo = $projecto->titulo;
        $projecto->delete();

        session()->flash('status', "Projecto SONA '{$titulo}' enviado para a lixeira com sucesso.");
    }

    public function render()
    {
        $user = Auth::user();
        $podeCriar = $user ? ($user->temPermissao('sona.criar') || $user->temPermissao('instrumentos.criar')) : false;
        $podeEditar = $user ? ($user->temPermissao('sona.editar_municipio') || $user->temPermissao('sona.editar') || $user->temPermissao('instrumentos.editar')) : false;
        $podeEliminar = $user ? ($user->temPermissao('sona.eliminar') || $user->temPermissao('instrumentos.eliminar')) : false;

        $baseQuery = ProjectoSona::query()->where('municipio_id', $this->municipio->id);

        $kpis = [
            'total' => (clone $baseQuery)->count(),
            'requalificacao' => (clone $baseQuery)->where('tipo', 'requalificacao')->count(),
            'avancos' => (clone $baseQuery)->where('tipo', 'avancos_obras')->count(),
            'em_curso' => (clone $baseQuery)->where('estado', 'em_curso')->count(),
            'concluido' => (clone $baseQuery)->where('estado', 'concluido')->count(),
            'suspenso' => (clone $baseQuery)->where('estado', 'suspenso')->count(),
        ];

        $query = (clone $baseQuery)->with(['ficheiros', 'criadoPor'])->latest();

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'like', $term)
                  ->orWhere('descricao', 'like', $term)
                  ->orWhere('fase', 'like', $term);
            });
        }

        if ($this->filtroTipo) {
            $query->where('tipo', $this->filtroTipo);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroFase) {
            $query->where('fase', $this->filtroFase);
        }

        $fasesDisponiveis = (clone $baseQuery)
            ->whereNotNull('fase')
            ->where('fase', '!=', '')
            ->distinct()
            ->pluck('fase')
            ->sort()
            ->values();

        $projectos = $query->paginate(12);

        return view('livewire.gestao-projecto-sona', [
            'projectos' => $projectos,
            'kpis' => $kpis,
            'fasesDisponiveis' => $fasesDisponiveis,
            'podeCriar' => $podeCriar,
            'podeEditar' => $podeEditar,
            'podeEliminar' => $podeEliminar,
        ]);
    }
}
