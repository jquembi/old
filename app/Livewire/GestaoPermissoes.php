<?php

namespace App\Livewire;

use App\Models\Permissao;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoPermissoes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $abaAtiva = 'regras';
    public bool $showDeleteModal = false;
    public bool $showRevokeModal = false;
    public ?array $revokeData = null;
    public bool $usuariosEmEdicao = false;
    public ?string $mostrarAjuda = null;

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('permissoes.gerir')) {
            $this->bloqueado = true;
            $this->motivo = 'Não tem permissão para ver permissões.';
        }
    }

    public function setAbaAtiva($aba)
    {
        $this->abaAtiva = $aba;
    }

    public function render()
    {
        $stats = [
            'total_regras' => 0,
            'total_permissoes' => 0,
            'total_usuarios_com_regras' => 0,
            'regras_sem_permissoes' => 0,
        ];
        $permissoes = Permissao::query()->where('id', 0)->paginate(25);
        $roles = collect();
        $usuariosComRole = User::query()->where('id', 0)->paginate(25);

        if (!($this->bloqueado ?? false)) {
            $query = Permissao::latest();

            if ($this->search) {
                $query->where('nome', 'like', '%' . $this->search . '%');
            }

            $permissoes = $query->paginate(25);
            $roles = Role::with('permissions')->get();
            $usuariosComRole = User::whereHas('roles')->paginate(25);

            $stats = [
                'total_regras' => $roles->count(),
                'total_permissoes' => $permissoes->total(),
                'total_usuarios_com_regras' => User::whereHas('roles')->count(),
                'regras_sem_permissoes' => $roles->filter(fn (Role $role) => $role->permissions->isEmpty())->count(),
            ];
        }

        return view('livewire.gestao-permissoes', [
            'permissoes' => $permissoes,
            'stats' => $stats,
            'roles' => $roles,
            'usuariosComRole' => $usuariosComRole,
        ]);
    }

    public function fecharModals()
    {
        $this->showDeleteModal = false;
        $this->showRevokeModal = false;
        $this->revokeData = null;
    }
}
