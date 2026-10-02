<?php

namespace App\Livewire;

use App\Models\Loteamento;
use Livewire\Component;

class LoteamentoDetalhe extends Component
{
    public Loteamento $loteamento;

    public function mount(Loteamento $loteamento)
    {
        $user = auth()->user();
        if ($user && $user->isMunicipal() && $user->municipio_id && $loteamento->municipio_id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a loteamentos deste município.');
        }

        $this->loteamento = $loteamento->load(['municipio', 'criadoPor', 'ficheiros']);
    }

    public function render()
    {
        return view('livewire.loteamento-detalhe');
    }
}
