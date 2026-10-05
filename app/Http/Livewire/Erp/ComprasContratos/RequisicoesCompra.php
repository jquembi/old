<?php

namespace App\Http\Livewire\Erp\ComprasContratos;

use App\Models\Departamento;
use App\Models\Funcionario;
use App\Models\RequisicaoCompra;
use Livewire\Component;
use Livewire\WithPagination;

class RequisicoesCompra extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $prioridade = '';

    public $modalOpen = false;

    public $requisicaoId = null;

    public $numero = '';

    public $data;

    public $descricao = '';

    public $prioridade_req = '';

    public $valor_estimado;

    public $departamento_id = null;

    public $solicitante_id = null;

    public $observacoes = '';

    protected $rules = [
        'numero' => 'required|string|max:30',
        'data' => 'required|date',
        'descricao' => 'required|string',
        'prioridade_req' => 'required|in:baixa,normal,alta,urgente',
        'valor_estimado' => 'nullable|numeric|min:0',
    ];

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = RequisicaoCompra::with(['departamento', 'solicitante', 'aprovador']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', '%'.$this->search.'%')
                    ->orWhere('descricao', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->prioridade) {
            $query->where('prioridade', $this->prioridade);
        }

        $requisicoes = $query->orderBy('data', 'desc')->paginate(15);
        $departamentos = Departamento::activo()->get();
        $solicitantes = Funcionario::ativo()->get();

        return view('livewire.erp.compras-contratos.requisicoes-compra', [
            'requisicoes' => $requisicoes,
            'departamentos' => $departamentos,
            'solicitantes' => $solicitantes,
        ]);
    }

    public function create()
    {
        $this->reset(['requisicaoId', 'numero', 'data', 'descricao', 'prioridade_req', 'valor_estimado', 'departamento_id', 'solicitante_id', 'observacoes']);
        $this->data = now()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(RequisicaoCompra $requisicao)
    {
        $this->requisicaoId = $requisicao->id;
        $this->numero = $requisicao->numero;
        $this->data = $requisicao->data->format('Y-m-d');
        $this->descricao = $requisicao->descricao;
        $this->prioridade_req = $requisicao->prioridade;
        $this->valor_estimado = $requisicao->valor_estimado;
        $this->departamento_id = $requisicao->departamento_id;
        $this->solicitante_id = $requisicao->solicitante_id;
        $this->observacoes = $requisicao->observacoes ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        RequisicaoCompra::updateOrCreate(
            ['id' => $this->requisicaoId],
            [
                'numero' => $this->numero,
                'data' => $this->data,
                'descricao' => $this->descricao,
                'prioridade' => $this->prioridade_req,
                'valor_estimado' => $this->valor_estimado,
                'departamento_id' => $this->departamento_id,
                'solicitante_id' => $this->solicitante_id,
                'observacoes' => $this->observacoes,
                'status' => 'rascunho',
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Requisição salva com sucesso!', type: 'success');
    }

    public function submeterParaAprovacao(RequisicaoCompra $requisicao)
    {
        $requisicao->submeterParaAprovacao();
        $this->dispatch('toast', message: 'Requisição submetida para aprovação!', type: 'success');
    }

    public function aprovar(RequisicaoCompra $requisicao)
    {
        $requisicao->aprovar(auth()->user()->funcionario);
        $this->dispatch('toast', message: 'Requisição aprovada!', type: 'success');
    }

    public function rejeitar(RequisicaoCompra $requisicao)
    {
        $requisicao->rejeitar(auth()->user()->funcionario);
        $this->dispatch('toast', message: 'Requisição rejeitada!', type: 'success');
    }

    public function delete(RequisicaoCompra $requisicao)
    {
        $requisicao->delete();
        $this->dispatch('toast', message: 'Requisição eliminada!', type: 'success');
    }
}
