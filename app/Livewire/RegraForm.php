<?php

namespace App\Livewire;

use App\Models\Permission;
use Livewire\Component;

class RegraForm extends Component
{
    public ?int $regraId = null;

    public function mount(?int $regra = null)
    {
        $this->regraId = $regra;
    }

    public function render()
    {
        return view('livewire.regra-form');
    }
}
