<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class UsuarioForm extends Component
{
    public ?int $userId = null;

    public function mount(?int $user = null)
    {
        $this->userId = $user;
    }

    public function render()
    {
        return view('livewire.usuario-form');
    }
}
