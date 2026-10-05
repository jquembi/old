<?php

namespace App\Http\Livewire\Erp\Patrimonio;

use App\Models\CategoriaPatrimonio;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriasPatrimonio extends Component
{
    use WithPagination;

    public $search = '';

    public $modalOpen = false;

    public $categoriaId = null;

    public $codigo = '';

    public $nome = '';

    public $descricao = '';

    public $categoria_pai_id = null;

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
        $query = CategoriaPatrimonio::with(['categoriaPai', 'categoriasFilhas']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%'.$this->search.'%')
                    ->orWhere('nome', 'like', '%'.$this->search.'%');
            });
        }

        $categorias = $query->orderBy('codigo')->paginate(15);
        $categoriasPai = CategoriaPatrimonio::raiz()->activo()->get();

        return view('livewire.erp.patrimonio.categorias-patrimonio', [
            'categorias' => $categorias,
            'categoriasPai' => $categoriasPai,
        ]);
    }

    public function create()
    {
        $this->reset(['categoriaId', 'codigo', 'nome', 'descricao', 'categoria_pai_id', 'activo']);
        $this->modalOpen = true;
    }

    public function edit(CategoriaPatrimonio $categoria)
    {
        $this->categoriaId = $categoria->id;
        $this->codigo = $categoria->codigo;
        $this->nome = $categoria->nome;
        $this->descricao = $categoria->descricao;
        $this->categoria_pai_id = $categoria->categoria_pai_id;
        $this->activo = $categoria->activo;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        CategoriaPatrimonio::updateOrCreate(
            ['id' => $this->categoriaId],
            [
                'codigo' => $this->codigo,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'categoria_pai_id' => $this->categoria_pai_id,
                'activo' => $this->activo,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Categoria salva com sucesso!', type: 'success');
    }

    public function delete(CategoriaPatrimonio $categoria)
    {
        if ($categoria->categoriasFilhas()->exists() || $categoria->patrimonios()->exists()) {
            $this->dispatch('toast', message: 'Não é possível eliminar categoria com subcategorias ou patrimónios!', type: 'error');

            return;
        }

        $categoria->delete();
        $this->dispatch('toast', message: 'Categoria eliminada!', type: 'success');
    }

    public function toggleActive(CategoriaPatrimonio $categoria)
    {
        $categoria->update(['activo' => ! $categoria->activo]);
        $this->dispatch('toast', message: 'Status atualizado!', type: 'success');
    }
}
