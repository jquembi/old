<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OutroProjecto;
use App\Models\ProjectoSona;
use Livewire\Component;

class ProjectosRequalificacao extends Component
{
    public Municipio $municipio;

    public function mount(Municipio $municipio): void
    {
        $this->municipio = $municipio;
    }

    public function render()
    {
        return view('livewire.projectos-requalificacao', [
            'municipio' => $this->municipio,
            'stats' => [
                'sona' => ProjectoSona::query()->where('municipio_id', $this->municipio->id)->count(),
                'outros' => OutroProjecto::query()->where('municipio_id', $this->municipio->id)->count(),
            ],
        ]);
    }
}
