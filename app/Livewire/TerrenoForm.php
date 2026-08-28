<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;

class TerrenoForm extends Component
{
    public Municipio $municipio;
    public ?int $croquiId = null;

    public function mount(Municipio $municipio, ?int $croqui = null)
    {
        $this->municipio = $municipio;
        $this->croquiId = $croqui;
    }

    public function render()
    {
        return view('livewire.terreno-form');
    }
}
