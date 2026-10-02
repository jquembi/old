<?php

namespace App\Livewire;

use App\Models\Instrumento;
use Livewire\Component;

class InstrumentoDetalhe extends Component
{
    public Instrumento $instrumento;

    public function mount(Instrumento $instrumento)
    {
        $user = auth()->user();
        if ($user && $user->isMunicipal() && $user->municipio_id && $instrumento->municipio_id !== $user->municipio_id) {
            abort(403, 'Não tem permissão para aceder a instrumentos deste município.');
        }

        $this->instrumento = $instrumento->load(['municipio', 'criadoPor', 'tipoInstrumento.categoria', 'ficheiros']);
    }

    public function render()
    {
        return view('livewire.instrumento-detalhe');
    }
}
