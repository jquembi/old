<?php

namespace App\Livewire;

use App\Models\Instrumento;
use App\Models\Municipio;
use App\Models\TipoInstrumento;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InstrumentosTipo extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $tipoSlug;
    public string $search = '';
    public string $filtroEstado = 'todos';
    public string $filtroAno = 'todos';
    public string $ordenacao = 'recente';

    public function mount(Municipio $municipio, string $tipoSlug): void
    {
        $this->municipio = $municipio;
        $this->tipoSlug = $tipoSlug;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroAno(): void
    {
        $this->resetPage();
    }

    public function updatingOrdenacao(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = 'todos';
        $this->filtroAno = 'todos';
        $this->ordenacao = 'recente';
        $this->resetPage();
    }

    public function eliminar(int $instrumentoId): void
    {
        $instrumento = $this->instrumentoQuery()->findOrFail($instrumentoId);
        LixeiraService::enviar($instrumento);

        $this->dispatch('notificacao', [
            'tipo' => 'info',
            'mensagem' => 'Instrumento enviado para a lixeira com sucesso.',
        ]);
    }

    public function render()
    {
        $tipo = TipoInstrumento::query()
            ->with('categoria')
            ->where('slug', $this->tipoSlug)
            ->firstOrFail();

        // Métricas e estatísticas do tipo atual
        $baseQuery = $this->instrumentoQuery();
        $totalGeral = (clone $baseQuery)->count();
        $totalAprovados = (clone $baseQuery)->where('estado', 'aprovado')->count();
        $totalSubmetidos = (clone $baseQuery)->where('estado', 'submetido')->count();
        $totalRascunhos = (clone $baseQuery)->where('estado', 'rascunho')->count();
        $totalComFicheiros = (clone $baseQuery)->has('ficheiros')->count();

        // Anos existentes para o filtro
        $anosDisponiveis = (clone $baseQuery)
            ->whereNotNull('ano')
            ->where('ano', '!=', '')
            ->distinct()
            ->orderBy('ano', 'desc')
            ->pluck('ano')
            ->all();

        // Consulta filtrada e ordenada
        $query = (clone $baseQuery)->with(['ficheiros', 'criadoPor']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descricao', 'like', '%' . $this->search . '%')
                    ->orWhere('ano', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtroEstado !== 'todos') {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroAno !== 'todos') {
            $query->where('ano', $this->filtroAno);
        }

        match ($this->ordenacao) {
            'antigo' => $query->oldest(),
            'ano_desc' => $query->orderBy('ano', 'desc')->latest(),
            'ano_asc' => $query->orderBy('ano', 'asc')->latest(),
            'titulo_asc' => $query->orderBy('titulo', 'asc'),
            default => $query->latest(),
        };

        return view('livewire.instrumentos-tipo', [
            'municipio' => $this->municipio,
            'tipo' => $tipo,
            'tipoSlug' => $this->tipoSlug,
            'instrumentos' => $query->paginate(15),
            'categoriaNome' => $tipo->categoria?->nome ?? 'Instrumentos',
            'categoriaRoute' => $this->categoriaRoute($tipo),
            'podeEditar' => Auth::user()?->temPermissao('instrumentos.criar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
            'stats' => [
                'total' => $totalGeral,
                'aprovados' => $totalAprovados,
                'submetidos' => $totalSubmetidos,
                'rascunhos' => $totalRascunhos,
                'comFicheiros' => $totalComFicheiros,
            ],
            'anosDisponiveis' => $anosDisponiveis,
        ]);
    }

    private function instrumentoQuery()
    {
        $tipoId = TipoInstrumento::query()->where('slug', $this->tipoSlug)->value('id');

        return Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->where('tipo_instrumento_id', $tipoId);
    }

    private function categoriaRoute(TipoInstrumento $tipo): string
    {
        return match ($tipo->categoria?->slug) {
            'ordenamento-territorio' => route('municipio.instrumentos-ordenamento', $this->municipio),
            'instrumentos-urbanisticos' => route('municipio.instrumentos-urbanisticos', $this->municipio),
            'cartas-municipais' => route('municipio.cartas', $this->municipio),
            default => route('municipio.show', $this->municipio),
        };
    }
}
