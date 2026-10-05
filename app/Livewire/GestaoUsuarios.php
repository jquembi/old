<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoUsuarios extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $filtroRoleId = null;
    public ?string $filtroMunicipioId = null;

    public bool $bloqueado = false;
    public ?string $motivo = null;

    public function mount()
    {
        // Verificar permissões no mount
        $user = auth()->user();
        if (!$user || !$user->temPermissao('usuarios.ver')) {
            $this->bloqueado = true;
            $this->motivo = 'Não tem permissão para ver utilizadores.';
        }
    }

    public function render()
    {
        $users = User::query()->where('id', 0)->paginate(25); // Empty pagination
        $podeGerir = false;
        $roles = collect();
        $municipios = collect();

        if (!$this->bloqueado) {
            $query = User::with('roles')->latest();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filtroRoleId) {
                $roleTable = config('rule-manager.table_names.model_has_roles', 'rule_manager_model_has_roles');
                $query->whereHas('roles', function ($q) use ($roleTable) {
                    $q->where("{$roleTable}.role_id", $this->filtroRoleId);
                });
            }

            if ($this->filtroMunicipioId) {
                $query->where('municipio_id', $this->filtroMunicipioId);
            }

            $users = $query->paginate(25);
            $podeGerir = auth()->user()?->temPermissao('usuarios.criar') || auth()->user()?->temPermissao('usuarios.editar');
            $roles = Role::all();
            $municipios = Municipio::all();
        }

        return view('livewire.gestao-usuarios', [
            'users' => $users,
            'usuarios' => $users,
            'podeGerir' => $podeGerir,
            'roles' => $roles,
            'municipios' => $municipios,
        ]);
    }

    public function toggleActivo($userId)
    {
        $actor = auth()->user();

        // Verificar permissão
        if (!$actor || !$actor->temPermissao('usuarios.editar')) {
            $msg = 'Não tem permissão para alterar o estado de utilizadores.';
            session()->flash('error', $msg);
            $this->dispatch('toast', type: 'error', message: $msg);
            return;
        }

        $user = User::findOrFail($userId);

        // Impedir que o utilizador se desative a si próprio
        if ($actor->id === $user->id) {
            $msg = 'Não pode desativar a sua própria conta.';
            session()->flash('error', $msg);
            $this->dispatch('toast', type: 'error', message: $msg);
            return;
        }

        // Verificar hierarquia: não pode alterar utilizadores do mesmo nível ou superior
        $actorNivel = $actor->nivelMinimo();
        $userNivel = $user->nivelMinimo();

        if ($actorNivel !== null && $userNivel !== null && $actorNivel >= $userNivel && !$actor->isDiretorInotu() && !$actor->isSuperAdminNacional()) {
            $msg = 'Não pode alterar utilizadores do mesmo nível ou superior.';
            session()->flash('error', $msg);
            $this->dispatch('toast', type: 'error', message: $msg);
            return;
        }

        $user->activo = !$user->activo;
        $user->save();

        $msg = $user->activo ? 'Utilizador ativado com sucesso.' : 'Utilizador desativado com sucesso.';
        session()->flash('message', $msg);
        $this->dispatch('toast', type: 'success', message: $msg);
    }
}
