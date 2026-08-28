<?php

namespace App\Livewire;

use App\Models\ProjectoSona;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoProjectoSona extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = ProjectoSona::latest();

        if ($this->search) {
            $query->where('titulo', 'like', '%' . $this->search . '%');
        }

        $projectos = $query->paginate(25);

        return view('livewire.gestao-projecto-sona', [
            'projectos' => $projectos,
        ]);
    }
}
