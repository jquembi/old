<?php

namespace App\Livewire;

use App\Models\Municipio;
use Livewire\Component;
use Livewire\WithPagination;

class ListaMunicipios extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Municipio::with('provincia')->latest();

        if ($this->search) {
            $query->where('nome', 'like', '%' . $this->search . '%');
        }

        $municipios = $query->paginate(25);

        return view('livewire.lista-municipios', [
            'municipios' => $municipios,
        ]);
    }
}
