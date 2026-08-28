<?php

namespace App\Livewire;

use App\Models\ArquivoBase;
use App\Models\Municipio;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AmbienteTrabalho extends Component
{
    use WithFileUploads, WithPagination;

    public Municipio $municipio;
    public string $search = '';
    public string $filtroTipo = '';
    public ?int $substituirId = null;
    public ?TemporaryUploadedFile $substituirFicheiro = null;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function trabalhar(int $arquivoId): void
    {
        $arquivo = $this->arquivoDoMunicipio($arquivoId);

        if (! $arquivo->estaEmUso() || (int) $arquivo->em_uso_por === Auth::id()) {
            $arquivo->bloquear((int) Auth::id());
        }
    }

    public function liberar(int $arquivoId): void
    {
        $this->arquivoDoMunicipio($arquivoId)->liberar((int) Auth::id());
    }

    public function abrirSubstituir(int $arquivoId): void
    {
        $this->arquivoDoMunicipio($arquivoId);
        $this->substituirId = $arquivoId;
        $this->substituirFicheiro = null;
    }

    public function cancelarSubstituir(): void
    {
        $this->substituirId = null;
        $this->substituirFicheiro = null;
    }

    public function substituirVersao(): void
    {
        $this->validate([
            'substituirFicheiro' => ['required', 'file', 'max:51200'],
        ]);

        $arquivo = $this->arquivoDoMunicipio((int) $this->substituirId);
        $ficheiro = $this->substituirFicheiro;

        if (! $ficheiro) {
            return;
        }

        if ($arquivo->ficheiro_caminho) {
            Storage::disk('public')->delete($arquivo->ficheiro_caminho);
        }

        $path = $ficheiro->store("ambiente-trabalho/{$this->municipio->id}", 'public');

        $arquivo->update([
            'ficheiro_caminho' => $path,
            'ficheiro_nome' => $ficheiro->getClientOriginalName(),
            'enviado_por' => Auth::id(),
            'em_uso_por' => null,
            'em_uso_desde' => null,
        ]);

        $this->cancelarSubstituir();
    }

    public function eliminar(int $arquivoId): void
    {
        LixeiraService::enviar($this->arquivoDoMunicipio($arquivoId));
    }

    public function render()
    {
        $query = ArquivoBase::query()
            ->with(['enviadoPor', 'emUsoPor'])
            ->where('municipio_id', $this->municipio->id);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('titulo', 'like', "%{$this->search}%")
                    ->orWhere('ficheiro_nome', 'like', "%{$this->search}%");
            });
        }

        if ($this->filtroTipo !== '') {
            $query->where('tipo', $this->filtroTipo);
        }

        return view('livewire.ambiente-trabalho', [
            'arquivos' => $query->latest()->paginate(25),
            'municipio' => $this->municipio,
            'podeEditar' => Auth::user()?->temPermissao('operacoes.editar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
        ]);
    }

    private function arquivoDoMunicipio(int $arquivoId): ArquivoBase
    {
        return ArquivoBase::query()
            ->where('municipio_id', $this->municipio->id)
            ->findOrFail($arquivoId);
    }
}
