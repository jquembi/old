<?php

namespace App\Livewire;

use App\Models\Parecer;
use Livewire\Component;

class ParecerDetalhe extends Component
{
    public Parecer $parecer;

    public function mount(Parecer $parecer)
    {
        $this->parecer = $parecer;
    }

    public function render()
    {
        return view('livewire.parecer-detalhe');
    }
}
