<?php

namespace App\Livewire;

use App\Models\Croqui;
use Livewire\Component;

class IdTerrenoDetalhe extends Component
{
    public Croqui $croqui;

    public function mount(Croqui $croqui)
    {
        $this->croqui = $croqui;
    }

    public function render()
    {
        return view('livewire.id-terreno-detalhe');
    }
}
