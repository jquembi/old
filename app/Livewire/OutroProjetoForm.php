<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;

class OutroProjetoForm extends Component
{
    public Municipio $municipio;
    public ?int $projetoId = null;

    public function mount(Municipio $municipio, ?int $projeto = null)
    {
        $this->municipio = $municipio;
        $this->projetoId = $projeto;
    }

    public function render()
    {
        return view('livewire.outro-projeto-form');
    }
}
