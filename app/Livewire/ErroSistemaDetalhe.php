<?php

namespace App\Livewire;

use App\Models\SystemError;
use Livewire\Component;

class ErroSistemaDetalhe extends Component
{
    public SystemError $error;

    public function mount(SystemError $error)
    {
        $this->error = $error;
    }

    public function render()
    {
        return view('livewire.erro-sistema-detalhe');
    }
}
