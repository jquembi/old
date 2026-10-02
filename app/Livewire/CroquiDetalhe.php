<?php

namespace App\Livewire;

use App\Models\Croqui;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CroquiDetalhe extends Component
{
    public Croqui $croqui;

    public function mount(Croqui $croqui)
    {
        abort_unless(Auth::user()?->can('view', $croqui), 403, 'Não tem permissão para visualizar este croqui.');

        $this->croqui = $croqui->load(['proprietarios', 'criadoPor', 'operacao.municipio', 'operacao.municipio.provincia']);
    }

    public function render()
    {
        return view('livewire.croqui-detalhe');
    }
}
