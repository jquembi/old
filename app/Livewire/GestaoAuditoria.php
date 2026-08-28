<?php

namespace App\Livewire;

use App\Models\Auditoria;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoAuditoria extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Auditoria::with('user')->latest();

        if ($this->search) {
            $query->where('acao', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        }

        $auditorias = $query->paginate(25);

        return view('livewire.gestao-auditoria', [
            'auditorias' => $auditorias,
        ]);
    }
}
