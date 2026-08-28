<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\InstrumentoFicheiro;
use App\Models\Municipio;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CartasMunicipais extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public ?int $detalheId = null;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function verDetalhe(int $cartaId): void
    {
        $this->detalheId = $cartaId;
    }

    public function fecharDetalhe(): void
    {
        $this->detalheId = null;
    }

    public function eliminar(int $cartaId): void
    {
        LixeiraService::enviar($this->cartaQuery()->findOrFail($cartaId));
        $this->fecharDetalhe();
    }

    public function eliminarFicheiro(int $ficheiroId): void
    {
        $ficheiro = InstrumentoFicheiro::query()
            ->whereHas('instrumento', fn ($query) => $query
                ->where('municipio_id', $this->municipio->id)
                ->whereIn('tipo_instrumento_id', $this->tipoCartaIds()))
            ->findOrFail($ficheiroId);

        $ficheiro->delete();
    }

    public function render()
    {
        $cartasQuery = $this->cartaQuery()
            ->with(['ficheiros.enviadoPor', 'criadoPor'])
            ->latest();

        if ($this->search !== '') {
            $cartasQuery->where(function ($query) {
                $query->where('titulo', 'like', "%{$this->search}%")
                    ->orWhere('descricao', 'like', "%{$this->search}%")
                    ->orWhere('ano', 'like', "%{$this->search}%");
            });
        }

        $detalhe = $this->detalheId
            ? $this->cartaQuery()->with(['ficheiros.enviadoPor', 'criadoPor'])->find($this->detalheId)
            : null;

        return view('livewire.cartas-municipais', [
            'municipio' => $this->municipio,
            'cartas' => $cartasQuery->paginate(12),
            'detalhe' => $detalhe,
            'podeEditar' => Auth::user()?->temPermissao('cartas_municipais.criar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
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
