<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class UsuarioForm extends Component
{
    public ?int $registoId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $role_ids = [];
    public ?int $municipio_id = null;
    public bool $activo = true;

    public function mount(mixed $usuario = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('usuarios.gerir')) {
            abort(403, 'Não tem permissão para gerir utilizadores.');
        }

        if ($usuario instanceof User) {
            $this->preencherUsuario($usuario);
        } elseif (is_numeric($usuario)) {
            $userModel = User::findOrFail((int) $usuario);
            $this->preencherUsuario($userModel);
        }
    }

    protected function preencherUsuario(User $user): void
    {
        $this->registoId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->municipio_id = $user->municipio_id;
        $this->activo = (bool) $user->activo;
        $this->role_ids = $user->roles()->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function rules(): array
    {
        $passwordRule = $this->registoId
            ? ['nullable', 'string', 'confirmed', Password::min(8)]
            : ['required', 'string', 'confirmed', Password::min(8)];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->registoId),
            ],
            'password' => $passwordRule,
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:rule_manager_roles,id'],
            'municipio_id' => ['nullable', 'integer', 'exists:municipios,id'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Introduza um email válido.',
            'email.unique' => 'Este email já se encontra registado.',
            'password.required' => 'A palavra-passe é obrigatória.',
            'password.confirmed' => 'A confirmação da palavra-passe não coincide.',
            'password.min' => 'A palavra-passe deve ter pelo menos 8 caracteres.',
            'role_ids.required' => 'Deve selecionar pelo menos uma função.',
            'role_ids.min' => 'Deve selecionar pelo menos uma função.',
        ];
    }

    public function guardar()
    {
        $user = auth()->user();
        if (!$user || !$user->temPermissao('usuarios.gerir')) {
            abort(403, 'Não tem permissão para gerir utilizadores.');
        }

        $validated = $this->validate();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'municipio_id' => $validated['municipio_id'] ?: null,
            'activo' => $this->activo,
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($this->registoId) {
            $targetUser = User::findOrFail($this->registoId);
            $targetUser->update($userData);
            $targetUser->roles()->sync($validated['role_ids']);
            session()->flash('status', "Utilizador '{$targetUser->name}' actualizado com sucesso.");
        } else {
            $targetUser = User::create($userData);
            $targetUser->roles()->sync($validated['role_ids']);
            session()->flash('status', "Utilizador '{$targetUser->name}' criado com sucesso.");
        }

        return redirect()->route('usuarios.index');
    }

    public function render()
    {
        return view('livewire.usuario-form', [
            'roles' => Role::all(),
            'municipios' => Municipio::orderBy('nome')->get(),
        ]);
    }
}
