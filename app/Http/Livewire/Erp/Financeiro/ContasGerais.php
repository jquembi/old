<?php

namespace App\Http\Livewire\Erp\Financeiro;

use App\Models\ContaGeral;
use Livewire\Component;
use Livewire\WithPagination;

class ContasGerais extends Component
{
    use WithPagination;

    public $search = '';
    public $tipo = '';
    public $nivel = '';
    public $modalOpen = false;
    public $contaId = null;
    public $codigo = '';
    public $nome = '';
    public $descricao = '';
    public $tipo_conta = '';
    public $nivel_conta = 1;
    public $conta_pai_id = null;
    public $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:20',
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'tipo_conta' => 'required|in:ativo,passivo,receita,despesa',
        'nivel_conta' => 'required|integer|min:1|max:5',
        'conta_pai_id' => 'nullable|exists:contas_gerais,id',
        'activo' => 'boolean',
    ];

    public function render()
    {
        $query = ContaGeral::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('nome', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        if ($this->nivel) {
            $query->where('nivel', $this->nivel);
        }

        $contas = $query->orderBy('codigo')->paginate(15);
        $contasPai = ContaGeral::whereNull('conta_pai_id')->activo()->get();

        return view('livewire.erp.financeiro.contas-gerais', [
            'contas' => $contas,
            'contasPai' => $contasPai,
        ]);
    }

    public function create()
    {
        $this->reset(['contaId', 'codigo', 'nome', 'descricao', 'tipo_conta', 'nivel_conta', 'conta_pai_id', 'activo']);
        $this->modalOpen = true;
    }

    public function edit(ContaGeral $conta)
    {
        $this->contaId = $conta->id;
        $this->codigo = $conta->codigo;
        $this->nome = $conta->nome;
        $this->descricao = $conta->descricao;
        $this->tipo_conta = $conta->tipo;
        $this->nivel_conta = $conta->nivel;
        $this->conta_pai_id = $conta->conta_pai_id;
        $this->activo = $conta->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        ContaGeral::updateOrCreate(
            ['id' => $this->contaId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'tipo' => $this->tipo_conta,
                'nivel' => $this->nivel_conta,
                'conta_pai_id' => $this->conta_pai_id,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('notify', message: 'Conta salva com sucesso!', type: 'success');
    }

    public function delete(ContaGeral $conta)
    {
        if ($conta->contasFilhas()->exists()) {
            $this->dispatch('notify', message: 'Não é possível eliminar conta com subcontas!', type: 'error');
            return;
        }

        $conta->delete();
        $this->dispatch('notify', message: 'Conta eliminada com sucesso!', type: 'success');
    }

    public function toggleActive(ContaGeral $conta)
    {
        $conta->update(['activo' => !$conta->activo]);
        $this->dispatch('notify', message: 'Status atualizado!', type: 'success');
    }
}
