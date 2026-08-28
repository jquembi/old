<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoUsuarios extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = User::with('roles')->latest();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        $users = $query->paginate(25);

        return view('livewire.gestao-usuarios', [
            'users' => $users,
        ]);
    }
}
