<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TerrenosOciosos extends Component
{
    use WithPagination;

    public Municipio $municipio;
    public string $search = '';

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function homologarDownload(int $croquiId): void
    {
        abort_unless(Auth::user()?->temPermissao('croquis.aprovar'), 403);

        $croqui = $this->croquisQuery()->findOrFail($croquiId);

        if (! $croqui->podeSerLiberado()) {
            $this->addError('homologar', 'O terreno precisa ter o RUP pago para ser homologado.');

            return;
        }

        $croqui->update([
            'download_liberado' => true,
            'liberado_por' => Auth::id(),
            'liberado_em' => now(),
        ]);
    }

    public function revogarDownload(int $croquiId): void
    {
        abort_unless(Auth::user()?->temPermissao('croquis.aprovar'), 403);

        $this->croquisQuery()->findOrFail($croquiId)->update([
            'download_liberado' => false,
            'liberado_por' => null,
            'liberado_em' => null,
        ]);
    }

    public function eliminar(int $croquiId): void
    {
        LixeiraService::enviar($this->croquisQuery()->findOrFail($croquiId));
    }

    public function render()
    {
        $croquisQuery = $this->croquisQuery()->with('criadoPor')->latest();

        if ($this->search !== '') {
            $croquisQuery->where(function ($query) {
                $query->where('titulo', 'like', "%{$this->search}%")
                    ->orWhere('ficheiro_pdf_nome', 'like', "%{$this->search}%");
            });
        }

        $user = Auth::user();

        return view('livewire.terrenos-ociosos', [
            'croquis' => $croquisQuery->paginate(25),
            'podeEditar' => $user?->temAlgumaPermissao([
                'operacoes.criar',
                'operacoes.editar_municipio',
                'operacoes.editar_proprio',
            ]) ?? false,
            'podeHomologar' => $user?->temPermissao('croquis.aprovar') ?? false,
            'podeEliminar' => $user?->podeEnviarParaLixeira() ?? false,
        ]);
    }

    private function croquisQuery()
    {
        return Croqui::query()
            ->where('tipo', 'terreno_ocioso')
            ->whereHas('operacao', fn ($query) => $query
                ->where('municipio_id', $this->municipio->id));
    }
}
