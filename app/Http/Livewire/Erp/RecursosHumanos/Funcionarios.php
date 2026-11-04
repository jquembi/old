<?php

namespace App\Http\Livewire\Erp\RecursosHumanos;

use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Funcionario;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class Funcionarios extends Component
{
    use WithPagination;

    public $search = '';

    public $departamento_id = '';

    public $cargo_id = '';

    public $status = '';

    public $modalOpen = false;

    public $funcionarioId = null;

    public $numero_funcionario = '';

    public $nome_completo = '';

    public $nome_curto = '';

    public $bi = '';

    public $data_nascimento;

    public $genero = '';

    public $telefone = '';

    public $email = '';

    public $endereco = '';

    public $departamento_id_form = '';

    public $cargo_id_form = '';

    public $data_admissao;

    public $salario_base;

    public $tipo_contrato = '';

    public $observacoes = '';

    public $user_id_form = '';

    public string $modo_utilizador = 'existente';

    public $user_password = '';

    public $user_role_id = '';

    protected $rules = [
        'numero_funcionario' => 'required|string|max:20|unique:funcionarios,numero_funcionario',
        'nome_completo' => 'required|string|max:255',
        'nome_curto' => 'required|string|max:100',
        'bi' => 'nullable|string|max:14',
        'data_nascimento' => 'required|date',
        'genero' => 'required|in:masculino,feminino',
        'telefone' => 'required|string|max:20',
        'email' => 'nullable|email',
        'departamento_id_form' => 'nullable|exists:departamentos,id',
        'cargo_id_form' => 'nullable|exists:cargos,id',
        'data_admissao' => 'required|date',
        'salario_base' => 'nullable|numeric|min:0',
        'tipo_contrato' => 'required|in:efetivo,contrato,estagio,temporario',
    ];

    protected function messages(): array
    {
        return [
            'user_id_form.required' => 'Selecione o utilizador vinculado ao funcionário.',
            'user_id_form.unique' => 'Este utilizador já está vinculado a outro funcionário.',
            'email.required' => 'O email é obrigatório para criar o utilizador.',
            'email.unique' => 'Este email já pertence a um utilizador.',
            'user_password.required' => 'Defina a palavra-passe do novo utilizador.',
            'user_role_id.required' => 'Selecione a função do novo utilizador.',
        ];
    }

    public function updated($property): void
    {
        if (! $this->modalOpen) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Funcionario::with(['departamento', 'cargo']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nome_completo', 'like', '%'.$this->search.'%')
                    ->orWhere('numero_funcionario', 'like', '%'.$this->search.'%')
                    ->orWhere('bi', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->departamento_id) {
            $query->where('departamento_id', $this->departamento_id);
        }

        if ($this->cargo_id) {
            $query->where('cargo_id', $this->cargo_id);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $funcionarios = $query->with('user')->orderBy('nome_completo')->paginate(15);
        $departamentos = Departamento::activo()->get();
        $cargos = Cargo::activo()->get();

        $utilizadoresLivres = User::query()
            ->whereDoesntHave('funcionario')
            ->when($this->funcionarioId, fn ($q) => $q->orWhereHas('funcionario', fn ($f) => $f->whereKey($this->funcionarioId)))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $actual = auth()->user();
        $funcoes = Role::all()->filter(fn ($r) => $actual?->isNacional() || $r->nivel >= $actual?->roleLevel());

        return view('livewire.erp.recursos-humanos.funcionarios', [
            'funcionarios' => $funcionarios,
            'departamentos' => $departamentos,
            'cargos' => $cargos,
            'utilizadoresLivres' => $utilizadoresLivres,
            'funcoes' => $funcoes,
        ]);
    }

    public function create()
    {
        $this->reset(['funcionarioId', 'numero_funcionario', 'nome_completo', 'nome_curto', 'bi', 'data_nascimento', 'genero', 'telefone', 'email', 'endereco', 'departamento_id_form', 'cargo_id_form', 'data_admissao', 'salario_base', 'tipo_contrato', 'observacoes', 'user_id_form', 'user_password', 'user_role_id']);
        $this->modo_utilizador = 'existente';
        $this->resetValidation();
        $this->data_nascimento = now()->subYears(25)->format('Y-m-d');
        $this->data_admissao = now()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(Funcionario $funcionario)
    {
        $this->resetValidation();
        $this->modo_utilizador = 'existente';
        $this->user_password = '';
        $this->user_role_id = '';
        $this->user_id_form = $funcionario->user_id ?? '';
        $this->funcionarioId = $funcionario->id;
        $this->numero_funcionario = $funcionario->numero_funcionario;
        $this->nome_completo = $funcionario->nome_completo;
        $this->nome_curto = $funcionario->nome_curto;
        $this->bi = $funcionario->bi;
        $this->data_nascimento = $funcionario->data_nascimento->format('Y-m-d');
        $this->genero = $funcionario->genero;
        $this->telefone = $funcionario->telefone;
        $this->email = $funcionario->email;
        $this->endereco = $funcionario->endereco;
        $this->departamento_id_form = $funcionario->departamento_id;
        $this->cargo_id_form = $funcionario->cargo_id;
        $this->data_admissao = $funcionario->data_admissao->format('Y-m-d');
        $this->salario_base = $funcionario->salario_base;
        $this->tipo_contrato = $funcionario->tipo_contrato;
        $this->observacoes = $funcionario->observacoes ?? '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->rules['numero_funcionario'] = 'required|string|max:20|unique:funcionarios,numero_funcionario,'.$this->funcionarioId;

        if ($this->modo_utilizador === 'novo' && ! $this->funcionarioId) {
            $this->rules['email'] = ['required', 'email', 'unique:users,email'];
            $this->rules['user_password'] = ['required', 'string', Password::min(8)];
            $this->rules['user_role_id'] = ['required', 'integer', 'exists:rule_manager_roles,id'];
        } else {
            $this->rules['user_id_form'] = ['required', 'integer', 'exists:users,id', Rule::unique('funcionarios', 'user_id')->ignore($this->funcionarioId)];
        }

        $this->validate();

        $actual = auth()->user();
        $userId = $this->user_id_form;

        if ($this->modo_utilizador === 'novo' && ! $this->funcionarioId) {
            $role = Role::findOrFail($this->user_role_id);
            if (! $actual->isNacional() && $role->nivel < $actual->roleLevel()) {
                $this->addError('user_role_id', 'Não tem permissão para atribuir esta função.');

                return;
            }

            $novo = User::create([
                'name' => $this->nome_completo,
                'email' => $this->email,
                'password' => Hash::make($this->user_password),
                'activo' => true,
                'criado_por' => $actual->id,
            ]);
            $novo->roles()->sync([$role->id]);
            $userId = $novo->id;
        }

        Funcionario::updateOrCreate(
            ['id' => $this->funcionarioId],
            [
                'user_id' => $userId,
                'numero_funcionario' => $this->numero_funcionario,
                'nome_completo' => $this->nome_completo,
                'nome_curto' => $this->nome_curto,
                'bi' => $this->bi,
                'data_nascimento' => $this->data_nascimento,
                'genero' => $this->genero,
                'telefone' => $this->telefone,
                'email' => $this->email,
                'endereco' => $this->endereco,
                'departamento_id' => $this->departamento_id_form ?: null,
                'cargo_id' => $this->cargo_id_form ?: null,
                'data_admissao' => $this->data_admissao,
                'salario_base' => $this->salario_base ?: null,
                'tipo_contrato' => $this->tipo_contrato,
                'status' => 'ativo',
                'observacoes' => $this->observacoes,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('toast', message: 'Funcionário salvo com sucesso!', type: 'success');
    }

    public function demitir(Funcionario $funcionario)
    {
        $funcionario->demitir();
        $this->dispatch('toast', message: 'Funcionário demitido!', type: 'success');
    }

    public function delete(Funcionario $funcionario)
    {
        $funcionario->delete();
        $this->dispatch('toast', message: 'Funcionário eliminado!', type: 'success');
    }
}
