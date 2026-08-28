<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;

class LoteamentoForm extends Component
{
    public Municipio $municipio;
    public ?int $loteamentoId = null;

    public function mount(Municipio $municipio, ?int $loteamento = null)
    {
        $this->municipio = $municipio;
        $this->loteamentoId = $loteamento;
    }

    public function render()
    {
        return view('livewire.loteamento-form');
    }
}
