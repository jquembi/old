<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\Parecer;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoPareceres extends Component
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

    public function eliminar(int $parecerId): void
    {
        $parecer = Parecer::query()
            ->where('municipio_id', $this->municipio->id)
            ->findOrFail($parecerId);

        LixeiraService::enviar($parecer);
    }

    public function render()
    {
        $query = Parecer::with(['recurso', 'criadoPor'])
            ->where('municipio_id', $this->municipio->id)
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('ficheiro_nome', 'like', '%' . $this->search . '%')
                    ->orWhere('projecto_nome', 'like', '%' . $this->search . '%');
            });
        }

        $pareceres = $query->paginate(25);

        return view('livewire.gestao-pareceres', [
            'pareceres' => $pareceres,
            'municipio' => $this->municipio,
            'podeEditar' => Auth::user()?->temPermissao('pareceres.editar') ?? false,
            'podeEliminar' => Auth::user()?->podeEnviarParaLixeira() ?? false,
        ]);
    }
}
