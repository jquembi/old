<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class LixeiraSistema extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tipo = '';

    public function render()
    {
        return view('livewire.lixeira-sistema');
    }
}
