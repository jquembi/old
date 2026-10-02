<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\ProjectoSona;
use App\Models\ProjectoSonaFicheiro;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjetoSonaForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public ?int $registoId = null;
    public ?ProjectoSona $projecto = null;

    public string $titulo = '';
    public ?string $descricao = null;
    public string $tipo = 'requalificacao';
    public ?string $fase = 'Fase 1';
    public string $estado = 'em_curso';
    public array $ficheiros = [];
    public ?string $faseFicheiro = null;
    public ?string $legenda = null;

    public function mount(Municipio $municipio, mixed $projecto = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $projecto ? 'sona.editar' : 'sona.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('sona.ver'))) {
            abort(403, 'Não tem permissão para aceder ao formulário de projectos SONA.');
        }

        if ($projecto instanceof ProjectoSona) {
            $this->projecto = $projecto;
            $this->carregarProjecto($projecto);
        } elseif ($projecto && is_numeric($projecto)) {
            $this->projecto = ProjectoSona::where('municipio_id', $municipio->id)->findOrFail((int) $projecto);
            $this->carregarProjecto($this->projecto);
        }
    }

    protected function carregarProjecto(ProjectoSona $projecto): void
    {
        $this->registoId = $projecto->id;
        $this->titulo = $projecto->titulo;
        $this->descricao = $projecto->descricao;
        $this->tipo = $projecto->tipo ?? 'requalificacao';
        $this->fase = $projecto->fase ?? 'Fase 1';
        $this->estado = $projecto->estado ?? 'em_curso';
    }

    public function getUploadProgressProperty(): int
    {
        return count($this->ficheiros) > 0 ? 100 : 0;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'tipo' => ['required', 'string', 'in:requalificacao,avancos_obras'],
            'fase' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'string', 'in:em_curso,concluido,suspenso'],
            'faseFicheiro' => ['nullable', 'string', 'max:100'],
            'legenda' => ['nullable', 'string', 'max:255'],
            'ficheiros.*' => ['nullable', 'file', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do projecto é obrigatório.',
            'tipo.required' => 'O tipo do projecto é obrigatório.',
            'estado.required' => 'O estado do projecto é obrigatório.',
            'ficheiros.*.max' => 'Cada ficheiro não pode exceder 50MB.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'sona.editar' : 'sona.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('sona.ver'))) {
            abort(403, 'Não tem permissão para gravar este projecto SONA.');
        }

        $validated = $this->validate();

        $dados = [
            'municipio_id' => $this->municipio->id,
            'titulo' => $validated['titulo'],
            'descricao' => $validated['descricao'] ?? null,
            'tipo' => $validated['tipo'],
            'fase' => $validated['fase'] ?? 'Fase 1',
            'estado' => $validated['estado'],
        ];

        if ($this->registoId) {
            $projecto = ProjectoSona::where('municipio_id', $this->municipio->id)->findOrFail($this->registoId);
            $projecto->update($dados);
            session()->flash('status', "Projecto SONA '{$projecto->titulo}' actualizado com sucesso.");
        } else {
            $dados['criado_por'] = Auth::id();
            $projecto = ProjectoSona::create($dados);
            session()->flash('status', "Projecto SONA '{$projecto->titulo}' criado com sucesso.");
        }

        if (!empty($this->ficheiros)) {
            foreach ($this->ficheiros as $ficheiro) {
                $caminho = $ficheiro->store("sona/{$this->municipio->id}", 'public');
                ProjectoSonaFicheiro::create([
                    'projecto_sona_id' => $projecto->id,
                    'nome_original' => $ficheiro->getClientOriginalName(),
                    'caminho' => $caminho,
                    'tipo_ficheiro' => $ficheiro->getClientOriginalExtension(),
                    'mime_type' => $ficheiro->getMimeType(),
                    'tamanho' => $ficheiro->getSize(),
                    'fase' => $this->faseFicheiro ?: $projecto->fase,
                    'legenda' => $this->legenda,
                    'enviado_por' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('municipio.requalificacao.sona', $this->municipio);
    }

    public function render()
    {
        return view('livewire.projeto-sona-form');
    }
}
