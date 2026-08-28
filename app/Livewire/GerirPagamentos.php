<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\Pagamento;
use Livewire\Component;
use Livewire\WithPagination;

class GerirPagamentos extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Pagamento::with(['croqui', 'croqui.operacao.municipio'])->latest();

        if ($this->search) {
            $query->where('rup_referencia', 'like', '%' . $this->search . '%');
        }

        $pagamentos = $query->paginate(25);

        return view('livewire.gerir-pagamentos', [
            'pagamentos' => $pagamentos,
        ]);
    }
}
