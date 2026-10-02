<?php

namespace App\Livewire;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RegraForm extends Component
{
    public ?int $registoId = null;
    public string $nome = '';
    public string $descricao = '';
    public int $nivel = 4;
    public string $grupo = 'Administração Municipal';

    public function mount(mixed $role = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('permissoes.gerir')) {
            abort(403, 'Não tem permissão para gerir regras.');
        }

        if ($role instanceof Role) {
            $this->preencherRole($role);
        } elseif (is_numeric($role)) {
            $roleModel = Role::findOrFail((int) $role);
            $this->preencherRole($roleModel);
        }
    }

    protected function preencherRole(Role $role): void
    {
        $this->registoId = $role->id;
        $this->nome = $role->nome;
        $this->descricao = $role->descricao ?? '';
        $this->nivel = $role->nivel ?? 4;
        $this->grupo = $role->grupo ?? 'Administração Municipal';
    }

    public function rules(): array
    {
        $roleModel = new Role();
        $tableName = $roleModel->getTable();

        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique($tableName, 'nome')->ignore($this->registoId),
            ],
            'descricao' => ['required', 'string', 'max:1000'],
            'nivel' => ['required', 'integer', 'between:0,5'],
            'grupo' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da regra é obrigatório.',
            'nome.regex' => 'O nome deve conter apenas letras minúsculas, números e sublinhados (snake_case).',
            'nome.unique' => 'Já existe uma regra com este nome.',
            'descricao.required' => 'A descrição da regra é obrigatória.',
            'nivel.required' => 'O nível de acesso é obrigatório.',
            'nivel.between' => 'O nível de acesso deve estar entre 0 e 5.',
            'grupo.required' => 'O grupo é obrigatório.',
        ];
    }

    public function guardar()
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('permissoes.gerir')) {
            abort(403, 'Não tem permissão para gerir regras.');
        }

        $validated = $this->validate();

        if ($this->registoId) {
            $role = Role::findOrFail($this->registoId);
            $role->update($validated);
            session()->flash('status', "Regra '{$role->nome}' actualizada com sucesso.");
        } else {
            $role = Role::create($validated);
            session()->flash('status', "Regra '{$role->nome}' criada com sucesso.");
        }

        return redirect()->route('permissoes.index');
    }

    public function render()
    {
        return view('livewire.regra-form');
    }
}
