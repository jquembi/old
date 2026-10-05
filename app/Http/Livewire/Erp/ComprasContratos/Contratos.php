<?php

namespace App\Http\Livewire\Erp\ComprasContratos;

use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\RequisicaoCompra;
use Livewire\Component;
use Livewire\WithPagination;

class Contratos extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $tipo = '';

    public $modalOpen = false;

    public $contratoId = null;

    public $numero = '';

    public $data_inicio;

    public $data_fim;

    public $descricao = '';

    public $valor_total;

    public $tipo_contrato = '';

    public $fornecedor_id = null;

    public $requisicao_compra_id = null;

    public $condicoes = '';

    public $arquivo_path = '';

    protected $rules = [
        'numero' => 'required|string|max:30',
        'data_inicio' => 'required|date',
        'data_fim' => 'required|date|after:data_inicio',
        'descricao' => 'required|string',
        'valor_total' => 'required|numeric|min:0',
        'tipo_contrato' => 'required|in:compra_bens,prestacao_servicos,obra,consultoria',
        'fornecedor_id' => 'required|exists:fornecedores,id',
    ];

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Contrato::with(['fornecedor', 'requisicaoCompra']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero', 'like', '%'.$this->search.'%')
                    ->orWhere('descricao', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $contratos = $query->orderBy('data_inicio', 'desc')->paginate(15);
        $fornecedores = Fornecedor::activo()->get();
        $requisicoes = RequisicaoCompra::porStatus(RequisicaoCompra::STATUS_APROVADO)->get();

        return view('livewire.erp.compras-contratos.contratos', [
            'contratos' => $contratos,
            'fornecedores' => $fornecedores,
            'requisicoes' => $requisicoes,
        ]);
    }

    public function create()
    {
        $this->reset(['contratoId', 'numero', 'data_inicio', 'data_fim', 'descricao', 'valor_total', 'tipo_contrato', 'fornecedor_id', 'requisicao_compra_id', 'condicoes', 'arquivo_path']);
        $this->data_inicio = now()->format('Y-m-d');
        $this->data_fim = now()->addYear()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(Contrato $contrato)
    {
        $this->contratoId = $contrato->id;
        $this->numero = $contrato->numero;
        $this->data_inicio = $contrato->data_inicio->format('Y-m-d');
        $this->data_fim = $contrato->data_fim->format('Y-m-d');
        $this->descricao = $contrato->descricao;
        $this->valor_total = $contrato->valor_total;
        $this->tipo_contrato = $contrato->tipo;
        $this->fornecedor_id = $contrato->fornecedor_id;
        $this->requisicao_compra_id = $contrato->requisicao_compra_id;
        $this->condicoes = $contrato->condicoes ?? '';
        $this->arquivo_path = $contrato->arquivo_path ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Contrato::updateOrCreate(
            ['id' => $this->contratoId],
            [
                'numero' => $this->numero,
                'data_inicio' => $this->data_inicio,
                'data_fim' => $this->data_fim,
                'descricao' => $this->descricao,
                'valor_total' => $this->valor_total,
                'tipo' => $this->tipo_contrato,
                'fornecedor_id' => $this->fornecedor_id,
                'requisicao_compra_id' => $this->requisicao_compra_id,
                'condicoes' => $this->condicoes,
                'arquivo_path' => $this->arquivo_path,
                'status' => 'rascunho',
                'created_by' => auth()->id(),
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Contrato salvo com sucesso!', type: 'success');
    }

    public function ativar(Contrato $contrato)
    {
        $contrato->update(['status' => 'ativo']);
        $this->dispatch('toast', message: 'Contrato ativado!', type: 'success');
    }

    public function suspender(Contrato $contrato)
    {
        $contrato->update(['status' => 'suspenso']);
        $this->dispatch('toast', message: 'Contrato suspenso!', type: 'success');
    }

    public function concluir(Contrato $contrato)
    {
        $contrato->update(['status' => 'concluido']);
        $this->dispatch('toast', message: 'Contrato concluído!', type: 'success');
    }

    public function delete(Contrato $contrato)
    {
        $contrato->delete();
        $this->dispatch('toast', message: 'Contrato eliminado!', type: 'success');
    }
}
