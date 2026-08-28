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

    public function mount(Municipio $municipio, string $tipoSlug): void
    {
        $this->municipio = $municipio;
        $this->tipoSlug = $tipoSlug;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function eliminar(int $instrumentoId): void
    {
        LixeiraService::enviar($this->instrumentoQuery()->findOrFail($instrumentoId));
    }

    public function render()
    {
        $tipo = TipoInstrumento::query()
            ->with('categoria')
            ->where('slug', $this->tipoSlug)
            ->firstOrFail();

        $query = $this->instrumentoQuery()
            ->with(['ficheiros', 'criadoPor'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descricao', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.instrumentos-tipo', [
            'municipio' => $this->municipio,
            'tipo' => $tipo,
            'tipoSlug' => $this->tipoSlug,
            'instrumentos' => $query->paginate(25),
            'categoriaNome' => $tipo->categoria?->nome ?? 'Instrumentos',
            'categoriaRoute' => $this->categoriaRoute($tipo),
            'podeEditar' => Auth::user()?->temPermissao('instrumentos.criar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
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
