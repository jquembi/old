<?php

namespace App\Http\Livewire\Erp\Patrimonio;

use App\Models\CategoriaPatrimonio;
use App\Models\Localizacao;
use App\Models\Patrimonio;
use Livewire\Component;
use Livewire\WithPagination;

class Patrimonios extends Component
{
    use WithPagination;

    public $search = '';

    public $categoria_id = '';

    public $localizacao_id = '';

    public $estado = '';

    public $status = '';

    public $modalOpen = false;

    public $patrimonioId = null;

    public $codigo = '';

    public $nome = '';

    public $descricao = '';

    public $categoria_patrimonio_id = null;

    public $localizacao_id_form = null;

    public $data_aquisicao;

    public $valor_aquisicao;

    public $valor_atual;

    public $estado_patrimonio = '';

    public $numero_serie = '';

    public $fornecedor = '';

    public $observacoes = '';

    protected $rules = [
        'codigo' => 'required|string|max:30',
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'categoria_patrimonio_id' => 'nullable|exists:categorias_patrimonio,id',
        'localizacao_id_form' => 'nullable|exists:localizacoes,id',
        'data_aquisicao' => 'required|date',
        'valor_aquisicao' => 'required|numeric|min:0',
        'valor_atual' => 'nullable|numeric|min:0',
        'estado_patrimonio' => 'required|in:novo,bom,razoavel,mau,danificado,obsoleto',
    ];

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Patrimonio::with(['categoriaPatrimonio', 'localizacao']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%'.$this->search.'%')
                    ->orWhere('nome', 'like', '%'.$this->search.'%')
                    ->orWhere('numero_serie', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->categoria_id) {
            $query->where('categoria_patrimonio_id', $this->categoria_id);
        }

        if ($this->localizacao_id) {
            $query->where('localizacao_id', $this->localizacao_id);
        }

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $patrimonios = $query->orderBy('codigo')->paginate(15);
        $categorias = CategoriaPatrimonio::activo()->get();
        $localizacoes = Localizacao::activo()->get();

        return view('livewire.erp.patrimonio.patrimonios', [
            'patrimonios' => $patrimonios,
            'categorias' => $categorias,
            'localizacoes' => $localizacoes,
        ]);
    }

    public function create()
    {
        $this->reset(['patrimonioId', 'codigo', 'nome', 'descricao', 'categoria_patrimonio_id', 'localizacao_id_form', 'data_aquisicao', 'valor_aquisicao', 'valor_atual', 'estado_patrimonio', 'numero_serie', 'fornecedor', 'observacoes']);
        $this->data_aquisicao = now()->format('Y-m-d');
        $this->estado_patrimonio = 'bom';
        $this->modalOpen = true;
    }

    public function edit(Patrimonio $patrimonio)
    {
        $this->patrimonioId = $patrimonio->id;
        $this->codigo = $patrimonio->codigo;
        $this->nome = $patrimonio->nome;
        $this->descricao = $patrimonio->descricao;
        $this->categoria_patrimonio_id = $patrimonio->categoria_patrimonio_id;
        $this->localizacao_id_form = $patrimonio->localizacao_id;
        $this->data_aquisicao = $patrimonio->data_aquisicao->format('Y-m-d');
        $this->valor_aquisicao = $patrimonio->valor_aquisicao;
        $this->valor_atual = $patrimonio->valor_atual;
        $this->estado_patrimonio = $patrimonio->estado;
        $this->numero_serie = $patrimonio->numero_serie;
        $this->fornecedor = $patrimonio->fornecedor;
        $this->observacoes = $patrimonio->observacoes ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Patrimonio::updateOrCreate(
            ['id' => $this->patrimonioId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'categoria_patrimonio_id' => $this->categoria_patrimonio_id,
                'localizacao_id' => $this->localizacao_id_form,
                'data_aquisicao' => $this->data_aquisicao,
                'valor_aquisicao' => $this->valor_aquisicao,
                'valor_atual' => $this->valor_atual,
                'estado' => $this->estado_patrimonio,
                'numero_serie' => $this->numero_serie,
                'fornecedor' => $this->fornecedor,
                'observacoes' => $this->observacoes,
                'status' => 'ativo',
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Património salvo com sucesso!', type: 'success');
    }

    public function delete(Patrimonio $patrimonio)
    {
        $patrimonio->delete();
        $this->dispatch('toast', message: 'Património eliminado!', type: 'success');
    }
}
