<?php

namespace App\Http\Livewire\Erp\RecursosHumanos;

use App\Models\Cargo;
use App\Models\Departamento;
use Livewire\Component;
use Livewire\WithPagination;

class Cargos extends Component
{
    use WithPagination;

    public $search = '';
    public $departamento_id = '';
    public $modalOpen = false;
    public $cargoId = null;
    public $codigo = '';
    public $nome = '';
    public $descricao = '';
    public $salario_minimo;
    public $salario_maximo;
    public $departamento_id_form = null;
    public $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:20',
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'salario_minimo' => 'nullable|numeric|min:0',
        'salario_maximo' => 'nullable|numeric|min:0',
        'activo' => 'boolean',
    ];

    public function render()
    {
        $query = Cargo::with(['departamento', 'funcionarios']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('nome', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->departamento_id) {
            $query->where('departamento_id', $this->departamento_id);
        }

        $cargos = $query->orderBy('codigo')->paginate(15);
        $departamentos = Departamento::activo()->get();

        return view('livewire.erp.recursos-humanos.cargos', [
            'cargos' => $cargos,
            'departamentos' => $departamentos,
        ]);
    }

    public function create()
    {
        $this->reset(['cargoId', 'codigo', 'nome', 'descricao', 'salario_minimo', 'salario_maximo', 'departamento_id_form', 'activo']);
        $this->modalOpen = true;
    }

    public function edit(Cargo $cargo)
    {
        $this->cargoId = $cargo->id;
        $this->codigo = $cargo->codigo;
        $this->nome = $cargo->nome;
        $this->descricao = $cargo->descricao;
        $this->salario_minimo = $cargo->salario_minimo;
        $this->salario_maximo = $cargo->salario_maximo;
        $this->departamento_id_form = $cargo->departamento_id;
        $this->activo = $cargo->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Cargo::updateOrCreate(
            ['id' => $this->cargoId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'salario_minimo' => $this->salario_minimo,
                'salario_maximo' => $this->salario_maximo,
                'departamento_id' => $this->departamento_id_form,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('notify', message: 'Cargo salvo com sucesso!', type: 'success');
    }

    public function delete(Cargo $cargo)
    {
        if ($cargo->funcionarios()->exists()) {
            $this->dispatch('notify', message: 'Não é possível eliminar cargo com funcionários!', type: 'error');
            return;
        }

        $cargo->delete();
        $this->dispatch('notify', message: 'Cargo eliminado!', type: 'success');
    }

    public function toggleActive(Cargo $cargo)
    {
        $cargo->update(['activo' => !$cargo->activo]);
        $this->dispatch('notify', message: 'Status atualizado!', type: 'success');
    }
}
