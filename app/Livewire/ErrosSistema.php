<?php

namespace App\Livewire;

use App\Models\SystemError;
use Livewire\Component;
use Livewire\WithPagination;

class ErrosSistema extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public ?string $dataInicio = null;

    public ?string $dataFim = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isNacional() || $user->isInotu() || $user->temPermissao('sistema.ver')), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'dataInicio', 'dataFim'], true)) {
            $this->resetPage();
        }
    }

    public function marcarResolvido(int $id): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isNacional() || $user->isInotu()), 403);
        SystemError::findOrFail($id)->update([
            'status' => 'resolvido', 'resolved_at' => now(), 'resolved_by' => $user->id,
        ]);
    }

    public function reabrir(int $id): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isNacional() || $user->isInotu()), 403);
        SystemError::findOrFail($id)->update([
            'status' => 'pendente', 'resolved_at' => null, 'resolved_by' => null,
        ]);
    }

    public function render()
    {
        $query = SystemError::with('user')->orderByDesc('last_seen_at');

        if ($this->search) {
            $query->where(function ($query) {
                foreach (['exception_class', 'message', 'url', 'file'] as $column) {
                    $query->orWhere($column, 'like', '%'.$this->search.'%');
                }
                $query->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->dataInicio) {
            $query->whereDate('last_seen_at', '>=', $this->dataInicio);
        }
        if ($this->dataFim) {
            $query->whereDate('last_seen_at', '<=', $this->dataFim);
        }

        $errors = $query->paginate(25);

        return view('livewire.erros-sistema', [
            'erros' => $errors,
            'total' => SystemError::count(),
            'pendentes' => SystemError::where('status', 'pendente')->count(),
            'resolvidos' => SystemError::where('status', 'resolvido')->count(),
            'recorrentes' => SystemError::where('occurrences', '>', 1)->count(),
            'ultimaFalha' => SystemError::max('last_seen_at'),
        ]);
    }
}
