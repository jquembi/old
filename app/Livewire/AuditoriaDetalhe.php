<?php

namespace App\Livewire;

use App\Models\Auditoria;
use Livewire\Component;

class AuditoriaDetalhe extends Component
{
    public Auditoria $auditoria;

    public function mount(Auditoria $auditoria)
    {
        $this->auditoria = $auditoria;
    }

    public function render()
    {
        return view('livewire.auditoria-detalhe');
    }
}
