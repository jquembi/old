<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use Livewire\Component;

class MemoriaDescritivaDetalhe extends Component
{
    public Municipio $municipio;
    public Croqui $croqui;

    public function mount(Municipio $municipio, Croqui $croqui)
    {
        $this->municipio = $municipio;
        $this->croqui = $croqui;
    }

    public function render()
    {
        return view('livewire.memoria-descritiva-detalhe');
    }
}
