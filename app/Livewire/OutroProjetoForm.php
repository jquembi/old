<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OutroProjecto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OutroProjetoForm extends Component
{
    public Municipio $municipio;
    public ?int $registoId = null;
    public ?OutroProjecto $projecto = null;

    public string $titulo = '';
    public string $tipo = 'requalificacao';
    public ?string $descricao = null;
    public ?string $entidade_responsavel = null;
    public ?string $fase = 'Fase 1';
    public string $estado = 'planificado';
    public ?string $data_inicio = null;
    public ?string $data_previsao_fim = null;
    public ?string $observacoes = null;

    public function mount(Municipio $municipio, mixed $projecto = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $projecto ? 'instrumentos.editar' : 'instrumentos.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('instrumentos.ver') && !$user->temPermissao('operacoes.ver'))) {
            abort(403, 'Não tem permissão para aceder ao formulário de outros projectos.');
        }

        if ($projecto instanceof OutroProjecto) {
            $this->projecto = $projecto;
            $this->carregarProjecto($projecto);
        } elseif ($projecto && is_numeric($projecto)) {
            $this->projecto = OutroProjecto::where('municipio_id', $municipio->id)->findOrFail((int) $projecto);
            $this->carregarProjecto($this->projecto);
        } else {
            $this->entidade_responsavel = 'Administração Municipal de ' . $municipio->nome;
        }
    }

    protected function carregarProjecto(OutroProjecto $projecto): void
    {
        $this->registoId = $projecto->id;
        $this->titulo = $projecto->titulo;
        $this->tipo = $projecto->tipo ?? 'requalificacao';
        $this->descricao = $projecto->descricao;
        $this->entidade_responsavel = $projecto->entidade_responsavel;
        $this->fase = $projecto->fase;
        $this->estado = $projecto->estado ?? 'planificado';
        $this->data_inicio = $projecto->data_inicio ? $projecto->data_inicio->format('Y-m-d') : null;
        $this->data_previsao_fim = $projecto->data_previsao_fim ? $projecto->data_previsao_fim->format('Y-m-d') : null;
        $this->observacoes = $projecto->observacoes;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:requalificacao,infraestrutura,social,outro'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'entidade_responsavel' => ['nullable', 'string', 'max:255'],
            'fase' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'string', 'in:planificado,em_curso,concluido,suspenso'],
            'data_inicio' => ['nullable', 'date'],
            'data_previsao_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do projecto é obrigatório.',
            'tipo.required' => 'O tipo do projecto é obrigatório.',
            'estado.required' => 'O estado do projecto é obrigatório.',
            'data_previsao_fim.after_or_equal' => 'A data de previsão deve ser posterior ou igual à data de início.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'instrumentos.editar' : 'instrumentos.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('instrumentos.ver') && !$user->temPermissao('operacoes.ver'))) {
            abort(403, 'Não tem permissão para gravar este projecto.');
        }

        $validated = $this->validate();

        $dados = [
            'municipio_id' => $this->municipio->id,
            'titulo' => $validated['titulo'],
            'tipo' => $validated['tipo'],
            'descricao' => $validated['descricao'] ?? null,
            'entidade_responsavel' => $validated['entidade_responsavel'] ?? null,
            'fase' => $validated['fase'] ?? null,
            'estado' => $validated['estado'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_previsao_fim' => $validated['data_previsao_fim'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
        ];

        if ($this->registoId) {
            $projecto = OutroProjecto::where('municipio_id', $this->municipio->id)->findOrFail($this->registoId);
            $projecto->update($dados);
            session()->flash('status', "Projecto '{$projecto->titulo}' actualizado com sucesso.");
        } else {
            $dados['criado_por'] = Auth::id();
            $projecto = OutroProjecto::create($dados);
            session()->flash('status', "Projecto '{$projecto->titulo}' criado com sucesso.");
        }

        return redirect()->route('municipio.requalificacao.outros', $this->municipio);
    }

    public function render()
    {
        return view('livewire.outro-projeto-form');
    }
}
