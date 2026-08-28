<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;

class ParecerForm extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        return view('livewire.parecer-form');
    }
}
