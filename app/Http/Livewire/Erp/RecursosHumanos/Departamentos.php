<?php

namespace App\Http\Livewire\Erp\RecursosHumanos;

use App\Models\Departamento;
use Livewire\Component;
use Livewire\WithPagination;

class Departamentos extends Component
{
    use WithPagination;

    public $search = '';

    public $modalOpen = false;

    public $departamentoId = null;

    public $codigo = '';

    public $nome = '';

    public $descricao = '';

    public $responsavel_id = null;

    public $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:20',
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
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
        $query = Departamento::with(['responsavel', 'funcionarios']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%'.$this->search.'%')
                    ->orWhere('nome', 'like', '%'.$this->search.'%');
            });
        }

        $departamentos = $query->orderBy('codigo')->paginate(15);

        return view('livewire.erp.recursos-humanos.departamentos', [
            'departamentos' => $departamentos,
        ]);
    }

    public function create()
    {
        $this->reset(['departamentoId', 'codigo', 'nome', 'descricao', 'responsavel_id', 'activo']);
        $this->modalOpen = true;
    }

    public function edit(Departamento $departamento)
    {
        $this->departamentoId = $departamento->id;
        $this->codigo = $departamento->codigo;
        $this->nome = $departamento->nome;
        $this->descricao = $departamento->descricao;
        $this->responsavel_id = $departamento->responsavel_id;
        $this->activo = $departamento->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Departamento::updateOrCreate(
            ['id' => $this->departamentoId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'responsavel_id' => $this->responsavel_id,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Departamento salvo com sucesso!', type: 'success');
    }

    public function delete(Departamento $departamento)
    {
        if ($departamento->funcionarios()->exists()) {
            $this->dispatch('toast', message: 'Não é possível eliminar departamento com funcionários!', type: 'error');

            return;
        }

        $departamento->delete();
        $this->dispatch('toast', message: 'Departamento eliminado!', type: 'success');
    }

    public function toggleActive(Departamento $departamento)
    {
        $departamento->update(['activo' => ! $departamento->activo]);
        $this->dispatch('toast', message: 'Status atualizado!', type: 'success');
    }
}
