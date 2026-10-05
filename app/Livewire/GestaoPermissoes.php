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

    public bool $bloqueado = false;
    public ?string $motivo = null;

    public string $search = '';
    public string $abaAtiva = 'regras';

    // Regras
    public string $nomeBuscaRoles = '';
    public ?int $roleEmEdicao = null;
    public array $permissoesSelectedRole = [];
    public ?int $roleParaEliminarId = null;

    // Permissões
    public string $nomeBuscaPermissoes = '';
    public string $filtroGrupoPermissoes = 'todos';
    public bool $showFormNovaPermissao = false;
    public string $novaPermissaoNome = '';
    public string $novaPermissaoDescricao = '';
    public string $novaPermissaoGrupo = '';
    public ?int $permissaoEmEdicao = null;
    public string $editPermissaoNome = '';
    public string $editPermissaoDescricao = '';
    public string $editPermissaoGrupo = '';

    // Utilizadores
    public int|bool $usuariosEmEdicao = false;
    public string $nomeBuscaUsuarios = '';
    public ?int $usuarioParaRevogarId = null;

    // Modals & UI
    public bool $showDeleteModal = false;
    public bool $showRevokeModal = false;
    public ?array $revokeData = null;
    public ?string $mostrarAjuda = null;

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('permissoes.gerir')) {
            $this->bloqueado = true;
            $this->motivo = 'Não tem permissão para gerir permissões.';
        }
    }

    public function setAbaAtiva(string $aba): void
    {
        $this->abaAtiva = $aba;
    }

    public function getNivelBadgeColorProperty(): array
    {
        return [
            0 => 'purple',
            1 => 'red',
            2 => 'amber',
            3 => 'blue',
            4 => 'emerald',
            5 => 'gray',
        ];
    }

    // ─── Métodos de Regras ────────────────────────────────────

    public function selecionarRole(int $roleId): void
    {
        if ($this->roleEmEdicao === $roleId) {
            $this->roleEmEdicao = null;
            $this->permissoesSelectedRole = [];
            return;
        }

        $role = Role::with('permissions')->find($roleId);
        if (!$role) {
            return;
        }

        $this->roleEmEdicao = $roleId;
        $this->permissoesSelectedRole = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function toggleTodasPermissoesDoGrupo(string $grupo, array $ids): void
    {
        $stringIds = array_map('strval', $ids);
        $allPresent = count(array_intersect($stringIds, $this->permissoesSelectedRole)) === count($stringIds);

        if ($allPresent) {
            $this->permissoesSelectedRole = array_values(array_diff($this->permissoesSelectedRole, $stringIds));
        } else {
            $this->permissoesSelectedRole = array_values(array_unique(array_merge($this->permissoesSelectedRole, $stringIds)));
        }
    }

    public function atualizarPermissoesRole(): void
    {
        if (!$this->roleEmEdicao) {
            return;
        }

        $role = Role::findOrFail($this->roleEmEdicao);
        $role->permissions()->sync($this->permissoesSelectedRole);

        $this->dispatch('toast', type: 'success', message: "Permissões da regra '{$role->nome}' atualizadas com sucesso.");
    }

    public function confirmarEliminarRegra(int $roleId): void
    {
        $this->roleParaEliminarId = $roleId;
        $this->showDeleteModal = true;
    }

    public function eliminarRegra(): void
    {
        if (!$this->roleParaEliminarId) {
            return;
        }

        $role = Role::findOrFail($this->roleParaEliminarId);

        // Prevenir eliminação de regras protegidas de sistema
        if (in_array($role->nome, [Role::SUPER_ADMIN_NACIONAL, Role::DIRECTOR_INOTU], true)) {
            $this->dispatch('toast', type: 'error', message: 'Não é permitido eliminar uma regra base do sistema.');
            $this->fecharModals();
            return;
        }

        $role->permissions()->detach();
        $role->delete();

        if ($this->roleEmEdicao === $this->roleParaEliminarId) {
            $this->roleEmEdicao = null;
            $this->permissoesSelectedRole = [];
        }

        $this->fecharModals();
        $this->dispatch('toast', type: 'success', message: "Regra '{$role->nome}' eliminada com sucesso.");
    }

    // ─── Métodos de Permissões ────────────────────────────────

    public function resetFormNovaPermissao(): void
    {
        $this->showFormNovaPermissao = false;
        $this->novaPermissaoNome = '';
        $this->novaPermissaoDescricao = '';
        $this->novaPermissaoGrupo = '';
        $this->resetValidation();
    }

    public function salvarNovaPermissao(): void
    {
        $this->validate([
            'novaPermissaoNome' => 'required|string|max:100|unique:rule_manager_permissions,nome',
            'novaPermissaoDescricao' => 'nullable|string|max:255',
            'novaPermissaoGrupo' => 'required|string|max:100',
        ], [
            'novaPermissaoNome.required' => 'O nome da permissão é obrigatório.',
            'novaPermissaoNome.unique' => 'Já existe uma permissão com este nome.',
            'novaPermissaoGrupo.required' => 'O grupo/módulo é obrigatório.',
        ]);

        $permissao = Permissao::create([
            'nome' => $this->novaPermissaoNome,
            'descricao' => $this->novaPermissaoDescricao,
            'modulo' => $this->novaPermissaoGrupo,
        ]);

        $this->resetFormNovaPermissao();
        $this->dispatch('toast', type: 'success', message: "Permissão '{$permissao->nome}' criada com sucesso.");
    }

    public function selecionarPermissaoParaEditar(int $id): void
    {
        $permissao = Permissao::findOrFail($id);
        $this->permissaoEmEdicao = $id;
        $this->editPermissaoNome = $permissao->nome;
        $this->editPermissaoDescricao = $permissao->descricao ?? '';
        $this->editPermissaoGrupo = $permissao->modulo ?? 'geral';
    }

    public function cancelarEdicaoPermissao(): void
    {
        $this->permissaoEmEdicao = null;
        $this->editPermissaoNome = '';
        $this->editPermissaoDescricao = '';
        $this->editPermissaoGrupo = '';
        $this->resetValidation();
    }

    public function atualizarPermissao(): void
    {
        if (!$this->permissaoEmEdicao) {
            return;
        }

        $this->validate([
            'editPermissaoNome' => "required|string|max:100|unique:rule_manager_permissions,nome,{$this->permissaoEmEdicao}",
            'editPermissaoDescricao' => 'nullable|string|max:255',
            'editPermissaoGrupo' => 'required|string|max:100',
        ]);

        $permissao = Permissao::findOrFail($this->permissaoEmEdicao);
        $permissao->update([
            'nome' => $this->editPermissaoNome,
            'descricao' => $this->editPermissaoDescricao,
            'modulo' => $this->editPermissaoGrupo,
        ]);

        $this->cancelarEdicaoPermissao();
        $this->dispatch('toast', type: 'success', message: "Permissão '{$permissao->nome}' atualizada com sucesso.");
    }

    public function eliminarPermissao(int $id): void
    {
        $permissao = Permissao::findOrFail($id);
        $nome = $permissao->nome;
        $permissao->roles()->detach();
        $permissao->delete();

        $this->dispatch('toast', type: 'success', message: "Permissão '{$nome}' eliminada com sucesso.");
    }

    // ─── Métodos de Utilizadores / Revogação ──────────────────

    public function verUsuariosComRole(int $roleId): void
    {
        if ($this->usuariosEmEdicao === $roleId) {
            $this->usuariosEmEdicao = false;
        } else {
            $this->usuariosEmEdicao = $roleId;
            $this->abaAtiva = 'utilizadores';
        }
    }

    public function confirmarRevogarRegra(int $userId): void
    {
        $this->usuarioParaRevogarId = $userId;
        $user = User::find($userId);
        $role = Role::find($this->usuariosEmEdicao);

        $this->revokeData = [
            'user' => $user,
            'role' => $role,
        ];
        $this->showRevokeModal = true;
    }

    public function revogarRegra(): void
    {
        if (!$this->usuarioParaRevogarId || !$this->usuariosEmEdicao) {
            return;
        }

        $user = User::findOrFail($this->usuarioParaRevogarId);
        $role = Role::findOrFail($this->usuariosEmEdicao);

        $user->roles()->detach($role->id);

        $this->fecharModals();
        $this->dispatch('toast', type: 'success', message: "Regra '{$role->nome}' revogada de {$user->name}.");
    }

    public function fecharModals(): void
    {
        $this->showDeleteModal = false;
        $this->showRevokeModal = false;
        $this->roleParaEliminarId = null;
        $this->usuarioParaRevogarId = null;
        $this->revokeData = null;
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        $stats = [
            'total_regras' => 0,
            'total_permissoes' => 0,
            'total_usuarios_com_regras' => 0,
            'regras_sem_permissoes' => 0,
        ];
        $permissoes = collect();
        $roles = collect();
        $usuariosComRole = User::query()->where('id', 0)->paginate(25);
        $gruposPermissoes = collect();

        if (!($this->bloqueado ?? false)) {
            // Regras
            $rolesQuery = Role::withCount(['users', 'permissions'])->with('permissions');
            if ($this->nomeBuscaRoles) {
                $rolesQuery->where(function ($q) {
                    $q->where('nome', 'like', '%' . $this->nomeBuscaRoles . '%')
                      ->orWhere('descricao', 'like', '%' . $this->nomeBuscaRoles . '%');
                });
            }
            $allRoles = $rolesQuery->get();
            $roles = $allRoles->groupBy(fn ($r) => $r->grupo ?: 'Geral');

            // Permissões
            $permissoesQuery = Permissao::query();
            if ($this->nomeBuscaPermissoes) {
                $permissoesQuery->where(function ($q) {
                    $q->where('nome', 'like', '%' . $this->nomeBuscaPermissoes . '%')
                      ->orWhere('descricao', 'like', '%' . $this->nomeBuscaPermissoes . '%');
                });
            }
            if ($this->filtroGrupoPermissoes !== 'todos') {
                $permissoesQuery->where('modulo', $this->filtroGrupoPermissoes);
            }
            $todasPermissoes = $permissoesQuery->orderBy('modulo')->orderBy('nome')->get();
            $permissoes = $todasPermissoes->groupBy(fn ($p) => $p->modulo ?: 'geral');

            $gruposPermissoes = Permissao::distinct()
                ->whereNotNull('modulo')
                ->where('modulo', '!=', '')
                ->pluck('modulo')
                ->sort()
                ->values();

            // Utilizadores da regra em edição
            if ($this->usuariosEmEdicao) {
                $roleModel = Role::find($this->usuariosEmEdicao);
                if ($roleModel) {
                    $usersQuery = $roleModel->users()->with('roles');
                    if ($this->nomeBuscaUsuarios) {
                        $usersQuery->where(function ($q) {
                            $q->where('name', 'like', '%' . $this->nomeBuscaUsuarios . '%')
                              ->orWhere('email', 'like', '%' . $this->nomeBuscaUsuarios . '%');
                        });
                    }
                    $usuariosComRole = $usersQuery->paginate(20);
                }
            }

            $stats = [
                'total_regras' => $allRoles->count(),
                'total_permissoes' => Permissao::count(),
                'total_usuarios_com_regras' => User::whereHas('roles')->count(),
                'regras_sem_permissoes' => $allRoles->filter(fn (Role $r) => $r->permissions_count === 0)->count(),
            ];
        }

        return view('livewire.gestao-permissoes', [
            'permissoes' => $permissoes,
            'stats' => $stats,
            'roles' => $roles,
            'usuariosComRole' => $usuariosComRole,
            'gruposPermissoes' => $gruposPermissoes,
        ]);
    }
}
