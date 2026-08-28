<?php

namespace App\Livewire;

use App\Models\LogAcesso;
use Livewire\Component;

class LogAcessoDetalhe extends Component
{
    public LogAcesso $log;

    public function mount(LogAcesso $log)
    {
        $this->log = $log;
    }

    public function render()
    {
        return view('livewire.log-acesso-detalhe');
    }
}
