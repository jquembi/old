<?php

namespace App\Livewire;

use App\Models\SystemError;
use Livewire\Component;
use Livewire\WithPagination;

class ErrosSistema extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = SystemError::latest();

        if ($this->search) {
            $query->where('message', 'like', '%' . $this->search . '%');
        }

        $errors = $query->paginate(25);

        return view('livewire.erros-sistema', [
            'errors' => $errors,
        ]);
    }
}
