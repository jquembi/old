<?php

namespace App\Livewire;

use App\Models\Parecer;
use Livewire\Component;

class ParecerDetalhe extends Component
{
    public Parecer $parecer;

    public function mount(Parecer $parecer)
    {
        abort_unless(auth()->user()?->can('view', $parecer), 403, 'Não tem permissão para visualizar este parecer.');

        $this->parecer = $parecer->load(['municipio', 'criadoPor', 'recurso']);
    }

    public function render()
    {
        return view('livewire.parecer-detalhe');
    }
}
