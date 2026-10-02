<?php

namespace App\Http\Livewire\Erp\RecursosHumanos;

use App\Models\Funcionario;
use App\Models\Departamento;
use App\Models\Cargo;
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

    public function render()
    {
        $query = Funcionario::with(['departamento', 'cargo']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nome_completo', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_funcionario', 'like', '%' . $this->search . '%')
                  ->orWhere('bi', 'like', '%' . $this->search . '%');
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

        $funcionarios = $query->orderBy('nome_completo')->paginate(15);
        $departamentos = Departamento::activo()->get();
        $cargos = Cargo::activo()->get();

        return view('livewire.erp.recursos-humanos.funcionarios', [
            'funcionarios' => $funcionarios,
            'departamentos' => $departamentos,
            'cargos' => $cargos,
        ]);
    }

    public function create()
    {
        $this->reset(['funcionarioId', 'numero_funcionario', 'nome_completo', 'nome_curto', 'bi', 'data_nascimento', 'genero', 'telefone', 'email', 'endereco', 'departamento_id_form', 'cargo_id_form', 'data_admissao', 'salario_base', 'tipo_contrato', 'observacoes']);
        $this->data_nascimento = now()->subYears(25)->format('Y-m-d');
        $this->data_admissao = now()->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function edit(Funcionario $funcionario)
    {
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
        $this->rules['numero_funcionario'] = 'required|string|max:20|unique:funcionarios,numero_funcionario,' . $this->funcionarioId;
        $this->validate();

        Funcionario::updateOrCreate(
            ['id' => $this->funcionarioId],
            [
                'numero_funcionario' => $this->numero_funcionario,
                'nome_completo' => $this->nome_completo,
                'nome_curto' => $this->nome_curto,
                'bi' => $this->bi,
                'data_nascimento' => $this->data_nascimento,
                'genero' => $this->genero,
                'telefone' => $this->telefone,
                'email' => $this->email,
                'endereco' => $this->endereco,
                'departamento_id' => $this->departamento_id_form,
                'cargo_id' => $this->cargo_id_form,
                'data_admissao' => $this->data_admissao,
                'salario_base' => $this->salario_base,
                'tipo_contrato' => $this->tipo_contrato,
                'status' => 'ativo',
                'observacoes' => $this->observacoes,
            ]
        );

        $this->modalOpen = false;
        $this->dispatch('notify', message: 'Funcionário salvo com sucesso!', type: 'success');
    }

    public function demitir(Funcionario $funcionario)
    {
        $funcionario->demitir();
        $this->dispatch('notify', message: 'Funcionário demitido!', type: 'success');
    }

    public function delete(Funcionario $funcionario)
    {
        $funcionario->delete();
        $this->dispatch('notify', message: 'Funcionário eliminado!', type: 'success');
    }
}
