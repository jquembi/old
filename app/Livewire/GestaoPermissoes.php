<?php

namespace App\Livewire;

use App\Models\Permissao;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoPermissoes extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Permissao::latest();

        if ($this->search) {
            $query->where('nome', 'like', '%' . $this->search . '%');
        }

        $permissoes = $query->paginate(25);

        return view('livewire.gestao-permissoes', [
            'permissoes' => $permissoes,
        ]);
    }
}
