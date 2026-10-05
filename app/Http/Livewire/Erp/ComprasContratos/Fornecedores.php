<?php

namespace App\Http\Livewire\Erp\ComprasContratos;

use App\Models\Fornecedor;
use Livewire\Component;
use Livewire\WithPagination;

class Fornecedores extends Component
{
    use WithPagination;

    public $search = '';

    public $tipo = '';

    public $modalOpen = false;

    public $fornecedorId = null;

    public $codigo = '';

    public $nome = '';

    public $nif = '';

    public $telefone = '';

    public $email = '';

    public $endereco = '';

    public $tipo_fornecedor = '';

    public $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:20',
        'nome' => 'required|string|max:255',
        'nif' => 'nullable|string|max:20',
        'telefone' => 'required|string|max:20',
        'email' => 'nullable|email',
        'tipo_fornecedor' => 'required|in:bens,servicos,ambos',
        'activo' => 'boolean',
    ];

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Fornecedor::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%'.$this->search.'%')
                    ->orWhere('nome', 'like', '%'.$this->search.'%')
                    ->orWhere('nif', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $fornecedores = $query->orderBy('nome')->paginate(15);

        return view('livewire.erp.compras-contratos.fornecedores', [
            'fornecedores' => $fornecedores,
        ]);
    }

    public function create()
    {
        $this->reset(['fornecedorId', 'codigo', 'nome', 'nif', 'telefone', 'email', 'endereco', 'tipo_fornecedor', 'activo']);
        $this->modalOpen = true;
    }

    public function edit(Fornecedor $fornecedor)
    {
        $this->fornecedorId = $fornecedor->id;
        $this->codigo = $fornecedor->codigo;
        $this->nome = $fornecedor->nome;
        $this->nif = $fornecedor->nif;
        $this->telefone = $fornecedor->telefone;
        $this->email = $fornecedor->email;
        $this->endereco = $fornecedor->endereco;
        $this->tipo_fornecedor = $fornecedor->tipo;
        $this->activo = $fornecedor->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Fornecedor::updateOrCreate(
            ['id' => $this->fornecedorId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'nif' => $this->nif,
                'telefone' => $this->telefone,
                'email' => $this->email,
                'endereco' => $this->endereco,
                'tipo' => $this->tipo_fornecedor,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Fornecedor salvo com sucesso!', type: 'success');
    }

    public function delete(Fornecedor $fornecedor)
    {
        if ($fornecedor->contratos()->exists()) {
            $this->dispatch('toast', message: 'Não é possível eliminar fornecedor com contratos!', type: 'error');

            return;
        }

        $fornecedor->delete();
        $this->dispatch('toast', message: 'Fornecedor eliminado!', type: 'success');
    }

    public function toggleActive(Fornecedor $fornecedor)
    {
        $fornecedor->update(['activo' => ! $fornecedor->activo]);
        $this->dispatch('toast', message: 'Status atualizado!', type: 'success');
    }
}
