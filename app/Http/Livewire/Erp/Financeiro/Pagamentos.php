<?php

namespace App\Http\Livewire\Erp\Financeiro;

use App\Models\PagamentoErp;
use Livewire\Component;
use Livewire\WithPagination;

class Pagamentos extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $metodo = '';
    public $modalOpen = false;
    public $pagamentoId = null;
    public $numero = '';
    public $data_pagamento;
    public $valor;
    public $metodo_pagamento = '';
    public $referencia = '';
    public $observacoes = '';

    protected $rules = [
        'numero' => 'required|string|max:30',
        'data_pagamento' => 'required|date',
        'valor' => 'required|numeric|min:0',
        'metodo_pagamento' => 'required|in:dinheiro,transferencia,multicaixa,cheque,cartao',
    ];

    public function render()
    {
        $query = PagamentoErp::with(['lancamento', 'taxaEmolumento', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', '%' . $this->search . '%')
                  ->orWhere('referencia', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->metodo) {
            $query->where('metodo_pagamento', $this->metodo);
        }

        $pagamentos = $query->orderBy('data_pagamento', 'desc')->paginate(15);

        return view('livewire.erp.financeiro.pagamentos', [
            'pagamentos' => $pagamentos,
        ]);
    }

    public function create()
    {
        $this->reset(['pagamentoId', 'numero', 'data_pagamento', 'valor', 'metodo_pagamento', 'referencia', 'observacoes']);
        $this->data_pagamento = now()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(PagamentoErp $pagamento)
    {
        $this->pagamentoId = $pagamento->id;
        $this->numero = $pagamento->numero;
        $this->data_pagamento = $pagamento->data_pagamento->format('Y-m-d');
        $this->valor = $pagamento->valor;
        $this->metodo_pagamento = $pagamento->metodo_pagamento;
        $this->referencia = $pagamento->referencia ?? '';
        $this->observacoes = $pagamento->observacoes ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        PagamentoErp::updateOrCreate(
            ['id' => $this->pagamentoId],
            [
                'numero' => $this->numero,
                'data_pagamento' => $this->data_pagamento,
                'valor' => $this->valor,
                'metodo_pagamento' => $this->metodo_pagamento,
                'referencia' => $this->referencia,
                'observacoes' => $this->observacoes,
                'status' => 'pendente',
                'user_id' => auth()->id(),
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('notify', message: 'Pagamento salvo com sucesso!', type: 'success');
    }

    public function confirmar(PagamentoErp $pagamento)
    {
        $pagamento->confirmar();
        $this->dispatch('notify', message: 'Pagamento confirmado!', type: 'success');
    }

    public function cancelar(PagamentoErp $pagamento)
    {
        $pagamento->cancelar();
        $this->dispatch('notify', message: 'Pagamento cancelado!', type: 'success');
    }

    public function delete(PagamentoErp $pagamento)
    {
        $pagamento->delete();
        $this->dispatch('notify', message: 'Pagamento eliminado!', type: 'success');
    }
}
