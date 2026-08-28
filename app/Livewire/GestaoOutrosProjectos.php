<?php

namespace App\Livewire;

use App\Models\OutroProjecto;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoOutrosProjectos extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = OutroProjecto::latest();

        if ($this->search) {
            $query->where('titulo', 'like', '%' . $this->search . '%');
        }

        $projectos = $query->paginate(25);

        return view('livewire.gestao-outros-projectos', [
            'projectos' => $projectos,
        ]);
    }
}
