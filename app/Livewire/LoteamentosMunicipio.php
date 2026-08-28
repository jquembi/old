<?php

namespace App\Livewire;

use App\Models\Loteamento;
use App\Models\Municipio;
use Livewire\Component;

class LoteamentosMunicipio extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        $loteamentos = Loteamento::where('municipio_id', $this->municipio->id)
            ->latest()
            ->paginate(25);

        return view('livewire.loteamentos-municipio', [
            'loteamentos' => $loteamentos,
        ]);
    }
}
