<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Municipio;
use Livewire\Component;

class MunicipioShow extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio->load('provincia');
    }

    public function render()
    {
        return view('livewire.municipio-show', [
            'stats' => $this->stats(),
        ]);
    }

    private function stats(): array
    {
        $operacaoIds = $this->municipio
            ->operacoesUrbanisticas()
            ->pluck('id');

        return [
            'instrumentos' => $this->municipio->instrumentos()->count(),
            'loteamentos' => $this->municipio->loteamentos()->count(),
            'croquis' => Croqui::query()->whereIn('operacao_id', $operacaoIds)->count(),
            'pareceres' => $this->municipio->pareceres()->count(),
            'sona' => $this->municipio->projectosSona()->count(),
            'outros' => $this->municipio->outrosProjectos()->count(),
        ];
    }
}
