<?php

namespace App\Http\Livewire\Nacional;

use App\Models\ParametroNacional;
use App\Models\Role;
use App\Services\AuditoriaCentralizadaService;
use Livewire\Component;

/**
 * Gere os parâmetros nacionais. No modo "regras" mostra apenas o grupo de
 * regras de governação e a matriz de perfis por nível.
 */
class Parametros extends Component
{
    public string $modo = 'parametros';

    public string $grupo = '';

    public string $search = '';

    /** @var array<int, string> */
    public array $valores = [];

    public function mount(string $modo = 'parametros'): void
    {
        $this->modo = $modo === 'regras' ? 'regras' : 'parametros';
        ParametroNacional::semear();
        $this->carregar();
    }

    private function carregar(): void
    {
        $this->valores = ParametroNacional::pluck('valor', 'id')->map(fn ($v) => (string) $v)->all();
    }

    private function podeEditar(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdminNacional() || $user?->isAdminNacional());
    }

    public function guardar(int $id): void
    {
        abort_unless($this->podeEditar(), 403);

        $parametro = ParametroNacional::findOrFail($id);
        abort_unless($parametro->editavel, 403);

        $valor = trim((string) ($this->valores[$id] ?? ''));

        if ($parametro->tipo === 'numero' && ! is_numeric($valor)) {
            $this->addError("valores.$id", 'Introduza um número válido.');

            return;
        }

        if ($parametro->tipo === 'numero' && (float) $valor < 0) {
            $this->addError("valores.$id", 'O valor não pode ser negativo.');

            return;
        }

        if ($parametro->tipo === 'booleano') {
            $valor = in_array($valor, ['1', 'true', 'on'], true) ? '1' : '0';
        }

        if ($parametro->tipo === 'texto' && $valor === '') {
            $this->addError("valores.$id", 'O valor é obrigatório.');

            return;
        }

        $this->resetErrorBag("valores.$id");
        $antes = $parametro->valor;

        if ($antes === $valor) {
            return;
        }

        $parametro->update(['valor' => $valor, 'atualizado_por' => auth()->id()]);
        $this->valores[$id] = $valor;

        app(AuditoriaCentralizadaService::class)->logUpdate(
            'parametro_nacional',
            $parametro->id,
            [$parametro->chave => $antes],
            [$parametro->chave => $valor],
            "Parâmetro nacional alterado: {$parametro->rotulo}",
        );

        $this->dispatch('toast', message: 'Parâmetro guardado.', type: 'success');
    }

    public function alternar(int $id): void
    {
        $atual = ($this->valores[$id] ?? '0') === '1';
        $this->valores[$id] = $atual ? '0' : '1';
        $this->guardar($id);
    }

    public function repor(int $id): void
    {
        abort_unless($this->podeEditar(), 403);

        $parametro = ParametroNacional::findOrFail($id);
        $this->valores[$id] = (string) $parametro->valor_padrao;
        $this->guardar($id);
    }

    public function render()
    {
        $regras = $this->modo === 'regras';

        $parametros = ParametroNacional::query()
            ->when($regras, fn ($q) => $q->where('grupo', 'regras'), fn ($q) => $q->where('grupo', '!=', 'regras'))
            ->when($this->grupo, fn ($q) => $q->where('grupo', $this->grupo))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('rotulo', 'like', "%{$this->search}%")
                ->orWhere('chave', 'like', "%{$this->search}%")))
            ->with('utilizador:id,name')
            ->orderBy('grupo')
            ->orderBy('id')
            ->get()
            ->groupBy('grupo');

        $perfis = $regras
            ? Role::query()->withCount(['users', 'permissoes'])
                ->orderBy('nivel')->orderBy('nome')->get()->groupBy('nivel')
            : collect();

        return view('livewire.nacional.parametros', [
            'parametros' => $parametros,
            'grupos' => ParametroNacional::GRUPOS,
            'perfis' => $perfis,
            'niveis' => [
                Role::NIVEL_NACIONAL => 'Nacional (super)',
                Role::NIVEL_ADMIN_NACIONAL => 'Administração nacional',
                Role::NIVEL_INOTU => 'INOTU',
                Role::NIVEL_PROVINCIAL => 'Provincial',
                Role::NIVEL_MUNICIPAL => 'Municipal',
                Role::NIVEL_TECNICO => 'Técnico',
            ],
            'podeEditar' => $this->podeEditar(),
            'regras' => $regras,
        ]);
    }
}
