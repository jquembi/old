<?php

namespace App\Livewire;

use App\Services\LixeiraService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LixeiraSistema extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tipo = 'todos';

    private const MODELOS = [
        'instrumentos' => [\App\Models\Instrumento::class, 'Instrumentos', 'titulo'],
        'loteamentos' => [\App\Models\Loteamento::class, 'Loteamentos', 'nome'],
        'operacoes' => [\App\Models\OperacaoUrbanistica::class, 'Operações urbanísticas', 'titulo'],
        'croquis' => [\App\Models\Croqui::class, 'Croquis', 'titulo'],
        'pareceres' => [\App\Models\Parecer::class, 'Pareceres', 'titulo'],
        'sona' => [\App\Models\ProjectoSona::class, 'Projectos SONA', 'titulo'],
        'outros_projectos' => [\App\Models\OutroProjecto::class, 'Outros projectos', 'titulo'],
        'arquivos' => [\App\Models\ArquivoBase::class, 'Arquivos base', 'titulo'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->podeAcederLixeira(), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo'], true)) {
            $this->resetPage();
        }
    }

    public function restaurar(string $tipo, int $id): void
    {
        abort_unless(isset(self::MODELOS[$tipo]), 404);
        LixeiraService::restaurar(self::MODELOS[$tipo][0]::onlyTrashed()->findOrFail($id));
    }

    public function eliminarDefinitivo(string $tipo, int $id): void
    {
        abort_unless(isset(self::MODELOS[$tipo]), 404);
        LixeiraService::eliminarDefinitivo(self::MODELOS[$tipo][0]::onlyTrashed()->findOrFail($id));
    }

    public function render()
    {
        abort_unless(auth()->user()?->podeAcederLixeira(), 403);
        abort_unless($this->tipo === 'todos' || isset(self::MODELOS[$this->tipo]), 404);

        $union = null;
        foreach (self::MODELOS as $tipo => [$model, $rotulo, $titulo]) {
            if ($this->tipo !== 'todos' && $this->tipo !== $tipo) {
                continue;
            }
            $table = (new $model)->getTable();
            $query = $model::onlyTrashed()
                ->leftJoin('users as deleted_users', 'deleted_users.id', '=', $table.'.deleted_by')
                ->select([$table.'.id', $table.'.'.$titulo.' as titulo', $table.'.deleted_at', 'deleted_users.name as deleted_by'])
                ->selectRaw('? as tipo, ? as rotulo', [$tipo, $rotulo]);
            if ($this->search) {
                $query->where($table.'.'.$titulo, 'like', '%'.$this->search.'%');
            }
            $base = $query->toBase();
            $union = $union ? $union->unionAll($base) : $base;
        }

        $itens = DB::query()->fromSub($union, 'itens_eliminados')->orderByDesc('deleted_at')->paginate(25);
        $itens->through(fn ($item) => [
            'id' => $item->id, 'titulo' => $item->titulo, 'tipo' => $item->tipo, 'rotulo' => $item->rotulo,
            'deleted_by' => $item->deleted_by ?? 'Utilizador indisponível',
            'deleted_at' => $item->deleted_at ? \Illuminate\Support\Carbon::parse($item->deleted_at) : null,
        ]);

        return view('livewire.lixeira-sistema', [
            'itens' => $itens,
            'tipos' => collect(self::MODELOS)->map(fn ($config) => $config[1]),
        ]);
    }
}
