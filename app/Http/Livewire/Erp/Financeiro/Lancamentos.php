<?php

namespace App\Http\Livewire\Erp\Financeiro;

use App\Models\ContaGeral;
use App\Models\Lancamento;
use Livewire\Component;
use Livewire\WithPagination;

class Lancamentos extends Component
{
    use WithPagination;

    public $search = '';

    public $tipo = '';

    public $status = '';

    public $modalOpen = false;

    public $lancamentoId = null;

    public $numero = '';

    public $data;

    public $conta_debito_id;

    public $conta_credito_id;

    public $valor;

    public $descricao = '';

    public $tipo_lancamento = '';

    public $observacoes = '';

    protected $rules = [
        'numero' => 'required|string|max:30',
        'data' => 'required|date',
        'conta_debito_id' => 'required|exists:contas_gerais,id',
        'conta_credito_id' => 'required|exists:contas_gerais,id',
        'valor' => 'required|numeric|min:0',
        'descricao' => 'required|string',
        'tipo_lancamento' => 'required|in:receita,despesa,transferencia',
    ];

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Lancamento::with(['contaDebito', 'contaCredito', 'createdBy']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', '%'.$this->search.'%')
                    ->orWhere('descricao', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $lancamentos = $query->orderBy('data', 'desc')->paginate(15);
        $contas = ContaGeral::activo()->get();

        return view('livewire.erp.financeiro.lancamentos', [
            'lancamentos' => $lancamentos,
            'contas' => $contas,
        ]);
    }

    public function create()
    {
        $this->reset(['lancamentoId', 'numero', 'data', 'conta_debito_id', 'conta_credito_id', 'valor', 'descricao', 'tipo_lancamento', 'observacoes']);
        $this->data = now()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(Lancamento $lancamento)
    {
        $this->lancamentoId = $lancamento->id;
        $this->numero = $lancamento->numero;
        $this->data = $lancamento->data->format('Y-m-d');
        $this->conta_debito_id = $lancamento->conta_debito_id;
        $this->conta_credito_id = $lancamento->conta_credito_id;
        $this->valor = $lancamento->valor;
        $this->descricao = $lancamento->descricao;
        $this->tipo_lancamento = $lancamento->tipo;
        $this->observacoes = $lancamento->observacoes ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Lancamento::updateOrCreate(
            ['id' => $this->lancamentoId],
            [
                'numero' => $this->numero,
                'data' => $this->data,
                'conta_debito_id' => $this->conta_debito_id,
                'conta_credito_id' => $this->conta_credito_id,
                'valor' => $this->valor,
                'descricao' => $this->descricao,
                'tipo' => $this->tipo_lancamento,
                'status' => 'pendente',
                'created_by' => auth()->id(),
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Lançamento salvo com sucesso!', type: 'success');
    }

    public function aprovar(Lancamento $lancamento)
    {
        $lancamento->aprovar(auth()->user());
        $this->dispatch('toast', message: 'Lançamento aprovado!', type: 'success');
    }

    public function cancelar(Lancamento $lancamento)
    {
        $lancamento->cancelar();
        $this->dispatch('toast', message: 'Lançamento cancelado!', type: 'success');
    }

    public function delete(Lancamento $lancamento)
    {
        $lancamento->delete();
        $this->dispatch('toast', message: 'Lançamento eliminado!', type: 'success');
    }
}
