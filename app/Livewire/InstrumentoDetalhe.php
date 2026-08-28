<?php

namespace App\Livewire;

use App\Models\Instrumento;
use Livewire\Component;

class InstrumentoDetalhe extends Component
{
    public Instrumento $instrumento;

    public function mount(Instrumento $instrumento)
    {
        $this->instrumento = $instrumento;
    }

    public function render()
    {
        return view('livewire.instrumento-detalhe');
    }
}
