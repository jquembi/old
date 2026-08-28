<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use Livewire\Component;

class TerrenosOciosos extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        $operacao = OperacaoUrbanistica::where('municipio_id', $this->municipio->id)
            ->where('tipo', 'terreno_ocioso')
            ->first();

        $croquis = $operacao ? $operacao->croquis()->where('tipo', 'terreno_ocioso')->latest()->paginate(25) : collect();

        return view('livewire.terrenos-ociosos', [
            'croquis' => $croquis,
        ]);
    }
}
