<?php

namespace App\Http\Livewire\Erp\Patrimonio;

use App\Models\Localizacao;
use App\Models\Departamento;
use Livewire\Component;
use Livewire\WithPagination;

class Localizacoes extends Component
{
    use WithPagination;

    public $search = '';
    public $tipo = '';
    public $modalOpen = false;
    public $localizacaoId = null;
    public $codigo = '';
    public $nome = '';
    public $descricao = '';
    public $departamento_id = null;
    public $tipo_localizacao = '';
    public $activo = true;

    protected $rules = [
        'codigo' => 'required|string|max:20',
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'tipo_localizacao' => 'required|in:edificio,sala,armazem,garagem,outro',
        'activo' => 'boolean',
    ];

    public function render()
    {
        $query = Localizacao::with(['departamento', 'patrimonios']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('nome', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $localizacoes = $query->orderBy('codigo')->paginate(15);
        $departamentos = Departamento::activo()->get();

        return view('livewire.erp.patrimonio.localizacoes', [
            'localizacoes' => $localizacoes,
            'departamentos' => $departamentos,
        ]);
    }

    public function create()
    {
        $this->reset(['localizacaoId', 'codigo', 'nome', 'descricao', 'departamento_id', 'tipo_localizacao', 'activo']);
        $this->tipo_localizacao = 'outro';
        $this->modalOpen = true;
    }

    public function edit(Localizacao $localizacao)
    {
        $this->localizacaoId = $localizacao->id;
        $this->codigo = $localizacao->codigo;
        $this->nome = $localizacao->nome;
        $this->descricao = $localizacao->descricao;
        $this->departamento_id = $localizacao->departamento_id;
        $this->tipo_localizacao = $localizacao->tipo;
        $this->activo = $localizacao->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Localizacao::updateOrCreate(
            ['id' => $this->localizacaoId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'departamento_id' => $this->departamento_id,
                'tipo' => $this->tipo_localizacao,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('notify', message: 'Localização salva com sucesso!', type: 'success');
    }

    public function delete(Localizacao $localizacao)
    {
        if ($localizacao->patrimonios()->exists()) {
            $this->dispatch('notify', message: 'Não é possível eliminar localização com patrimónios!', type: 'error');
            return;
        }

        $localizacao->delete();
        $this->dispatch('notify', message: 'Localização eliminada!', type: 'success');
    }

    public function toggleActive(Localizacao $localizacao)
    {
        $localizacao->update(['activo' => !$localizacao->activo]);
        $this->dispatch('notify', message: 'Status atualizado!', type: 'success');
    }
}
