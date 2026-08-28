<?php

namespace App\Livewire;

use App\Models\Pagamento;
use Livewire\Component;

class PagamentoDetalhe extends Component
{
    public Pagamento $pagamento;

    public function mount(Pagamento $pagamento)
    {
        $this->pagamento = $pagamento;
    }

    public function render()
    {
        return view('livewire.pagamento-detalhe');
    }
}
