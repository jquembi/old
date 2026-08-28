<?php

namespace App\Livewire;

use App\Models\Loteamento;
use Livewire\Component;

class LoteamentoDetalhe extends Component
{
    public Loteamento $loteamento;

    public function mount(Loteamento $loteamento)
    {
        $this->loteamento = $loteamento;
    }

    public function render()
    {
        return view('livewire.loteamento-detalhe');
    }
}
