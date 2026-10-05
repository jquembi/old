<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\InstrumentoFicheiro;
use App\Models\Municipio;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class CartasMunicipais extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public string $filtroEstado = 'todos';
    public string $filtroAno = 'todos';
    public string $ordenacao = 'recente';
    public string $visualizacao = 'grade'; // 'grade' ou 'tabela'
    public ?int $detalheId = null;
    public ?string $ficheiroAtivoCaminho = null;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
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

    public function setVisualizacao(string $modo): void
    {
        $this->visualizacao = in_array($modo, ['grade', 'tabela']) ? $modo : 'grade';
    }

    public function limparFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = 'todos';
        $this->filtroAno = 'todos';
        $this->ordenacao = 'recente';
        $this->resetPage();
    }

    public function verDetalhe(int $cartaId): void
    {
        $this->detalheId = $cartaId;
        $detalhe = $this->cartaQuery()->with('ficheiros')->find($cartaId);

        $primeiroPdf = $detalhe?->ficheiros->first(fn ($f) => strtolower($f->tipo_ficheiro) === 'pdf');
        $this->ficheiroAtivoCaminho = $primeiroPdf ? $primeiroPdf->caminho : $detalhe?->ficheiros->first()?->caminho;
    }

    public function selecionarFicheiroAtivo(string $caminho): void
    {
        $this->ficheiroAtivoCaminho = $caminho;
    }

    public function fecharDetalhe(): void
    {
        $this->detalheId = null;
        $this->ficheiroAtivoCaminho = null;
    }

    public function eliminar(int $cartaId): void
    {
        $carta = $this->cartaQuery()->findOrFail($cartaId);
        LixeiraService::enviar($carta);
        $this->fecharDetalhe();

        $this->dispatch('notificacao', [
            'tipo' => 'info',
            'mensagem' => 'Carta municipal enviada para a lixeira com sucesso.',
        ]);
    }

    public function eliminarFicheiro(int $ficheiroId): void
    {
        $ficheiro = InstrumentoFicheiro::query()
            ->whereHas('instrumento', fn ($query) => $query
                ->where('municipio_id', $this->municipio->id)
                ->whereIn('tipo_instrumento_id', $this->tipoCartaIds()))
            ->findOrFail($ficheiroId);

        Storage::disk('public')->delete($ficheiro->caminho);
        $ficheiro->delete();

        if ($this->detalheId) {
            $this->verDetalhe($this->detalheId);
        }

        $this->dispatch('notificacao', [
            'tipo' => 'success',
            'mensagem' => 'Ficheiro cartográfico removido com sucesso.',
        ]);
    }

    public function render()
    {
        $baseQuery = $this->cartaQuery();

        // Métricas rápidas
        $totalGeral = (clone $baseQuery)->count();
        $totalAprovadas = (clone $baseQuery)->where('estado', 'aprovado')->count();
        $totalSubmetidas = (clone $baseQuery)->where('estado', 'submetido')->count();
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
        $cartasQuery = (clone $baseQuery)->with(['ficheiros.enviadoPor', 'criadoPor']);

        if ($this->search !== '') {
            $cartasQuery->where(function ($query) {
                $query->where('titulo', 'like', "%{$this->search}%")
                    ->orWhere('descricao', 'like', "%{$this->search}%")
                    ->orWhere('ano', 'like', "%{$this->search}%");
            });
        }

        if ($this->filtroEstado !== 'todos') {
            $cartasQuery->where('estado', $this->filtroEstado);
        }

        if ($this->filtroAno !== 'todos') {
            $cartasQuery->where('ano', $this->filtroAno);
        }

        match ($this->ordenacao) {
            'antigo' => $cartasQuery->oldest(),
            'ano_desc' => $cartasQuery->orderBy('ano', 'desc')->latest(),
            'ano_asc' => $cartasQuery->orderBy('ano', 'asc')->latest(),
            'titulo_asc' => $cartasQuery->orderBy('titulo', 'asc'),
            default => $cartasQuery->latest(),
        };

        $detalhe = $this->detalheId
            ? $this->cartaQuery()->with(['ficheiros.enviadoPor', 'criadoPor'])->find($this->detalheId)
            : null;

        return view('livewire.cartas-municipais', [
            'municipio' => $this->municipio,
            'cartas' => $cartasQuery->paginate($this->visualizacao === 'grade' ? 12 : 15),
            'detalhe' => $detalhe,
            'podeEditar' => Auth::user()?->temPermissao('cartas_municipais.criar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
            'stats' => [
                'total' => $totalGeral,
                'aprovadas' => $totalAprovadas,
                'submetidas' => $totalSubmetidas,
                'comFicheiros' => $totalComFicheiros,
            ],
            'anosDisponiveis' => $anosDisponiveis,
        ]);
    }

    private function cartaQuery()
    {
        return Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->whereIn('tipo_instrumento_id', $this->tipoCartaIds());
    }

    private function tipoCartaIds(): array
    {
        $categoria = CategoriaInstrumento::query()
            ->where('slug', 'cartas-municipais')
            ->with('tipoInstrumentos:id,categoria_id')
            ->first();

        return $categoria?->tipoInstrumentos->pluck('id')->all() ?? [];
    }
}
