<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\Parecer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ParecerForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public ?int $registoId = null;
    public ?Parecer $parecer = null;

    public string $titulo = '';
    public ?string $projecto_nome = null;
    public ?string $servico = null;
    public ?string $entidade_requerente = null;
    public ?string $tipo_concessao = null;
    public string $visibilidade = 'privado';
    public ?string $classificacao_area = null;
    public ?string $area_reservada = null;
    public ?string $uso_solo = null;
    public ?string $zona_ordenamento = null;
    public ?string $fundamentacao = null;
    public ?string $conclusao = null;
    public ?string $recursoTitulo = null;

    public $ficheiro = null;
    public ?string $ficheiroAtualCaminho = null;
    public ?string $ficheiroAtualNome = null;
    public bool $isUploading = false;

    public function mount(Municipio $municipio, mixed $parecer = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $parecer ? 'pareceres.editar' : 'pareceres.criar';
        if (!$user || !$user->temPermissao($permissaoNecessaria)) {
            abort(403, 'Não tem permissão para aceder ao formulário de parecer.');
        }

        if ($parecer instanceof Parecer) {
            $this->parecer = $parecer;
            $this->carregarParecer($parecer);
        } elseif ($parecer && is_numeric($parecer)) {
            $this->parecer = Parecer::where('municipio_id', $municipio->id)->findOrFail((int) $parecer);
            $this->carregarParecer($this->parecer);
        }
    }

    protected function carregarParecer(Parecer $parecer): void
    {
        $this->registoId = $parecer->id;
        $this->titulo = $parecer->titulo ?? '';
        $this->projecto_nome = $parecer->projecto_nome;
        $this->servico = $parecer->servico;
        $this->entidade_requerente = $parecer->entidade_requerente;
        $this->tipo_concessao = $parecer->tipo_concessao;
        $this->visibilidade = $parecer->visibilidade ?? 'privado';
        $this->classificacao_area = $parecer->classificacao_area;
        $this->area_reservada = $parecer->area_reservada !== null ? ($parecer->area_reservada ? '1' : '0') : null;
        $this->uso_solo = $parecer->uso_solo;
        $this->zona_ordenamento = $parecer->zona_ordenamento;
        $this->fundamentacao = $parecer->fundamentacao;
        $this->conclusao = $parecer->conclusao;
        $this->ficheiroAtualCaminho = $parecer->ficheiro_caminho;
        $this->ficheiroAtualNome = $parecer->ficheiro_nome;
        $this->recursoTitulo = $parecer->recurso?->titulo ?? $parecer->projecto_nome;
    }

    public function getUploadProgressProperty(): int
    {
        return $this->ficheiro ? 100 : 0;
    }

    public function removerFicheiroAtual(): void
    {
        $user = Auth::user();
        if (!$user || !$user->podeEnviarParaLixeira()) {
            abort(403, 'Não tem permissão para remover ficheiros.');
        }

        if ($this->parecer && $this->parecer->ficheiro_caminho) {
            Storage::disk('public')->delete($this->parecer->ficheiro_caminho);
            $this->parecer->update([
                'ficheiro_caminho' => null,
                'ficheiro_nome' => null,
            ]);
            $this->ficheiroAtualCaminho = null;
            $this->ficheiroAtualNome = null;
        }
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'projecto_nome' => ['nullable', 'string', 'max:255'],
            'servico' => ['nullable', 'string', 'max:100'],
            'entidade_requerente' => ['nullable', 'string', 'max:255'],
            'tipo_concessao' => ['nullable', 'string', 'max:255'],
            'visibilidade' => ['required', 'string', 'in:privado,publico'],
            'classificacao_area' => ['nullable', 'string', 'max:255'],
            'area_reservada' => ['nullable', 'in:0,1'],
            'uso_solo' => ['nullable', 'string', 'max:255'],
            'zona_ordenamento' => ['nullable', 'string', 'max:255'],
            'fundamentacao' => ['nullable', 'string', 'max:10000'],
            'conclusao' => ['nullable', 'string', 'max:10000'],
            'ficheiro' => $this->registoId ? ['nullable', 'file', 'mimes:pdf,docx', 'max:51200'] : ['nullable', 'file', 'mimes:pdf,docx', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do parecer é obrigatório.',
            'ficheiro.mimes' => 'O ficheiro deve ser um documento PDF ou Word (.docx).',
            'ficheiro.max' => 'O tamanho máximo do ficheiro é de 50MB.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'pareceres.editar' : 'pareceres.criar';
        if (!$user || !$user->temPermissao($permissaoNecessaria)) {
            abort(403, 'Não tem permissão para gravar este parecer.');
        }

        $validated = $this->validate();

        $dados = [
            'municipio_id' => $this->municipio->id,
            'titulo' => $validated['titulo'],
            'slug' => Str::slug($validated['titulo']) . '-' . Str::random(6),
            'projecto_nome' => $validated['projecto_nome'] ?? null,
            'servico' => $validated['servico'] ?? null,
            'entidade_requerente' => $validated['entidade_requerente'] ?? null,
            'tipo_concessao' => $validated['tipo_concessao'] ?? null,
            'visibilidade' => $validated['visibilidade'] ?? 'privado',
            'classificacao_area' => $validated['classificacao_area'] ?? null,
            'area_reservada' => $this->area_reservada !== null && $this->area_reservada !== '' ? (bool) (int) $this->area_reservada : null,
            'uso_solo' => $validated['uso_solo'] ?? null,
            'zona_ordenamento' => $validated['zona_ordenamento'] ?? null,
            'fundamentacao' => $validated['fundamentacao'] ?? null,
            'conclusao' => $validated['conclusao'] ?? null,
        ];

        if ($this->ficheiro) {
            $caminho = $this->ficheiro->store("pareceres/{$this->municipio->id}", 'public');
            $dados['ficheiro_caminho'] = $caminho;
            $dados['ficheiro_nome'] = $this->ficheiro->getClientOriginalName();
        }

        if ($this->registoId) {
            $parecer = Parecer::where('municipio_id', $this->municipio->id)->findOrFail($this->registoId);
            $parecer->update($dados);
            session()->flash('status', "Parecer '{$parecer->titulo}' actualizado com sucesso.");
        } else {
            $dados['criado_por'] = $user->id;
            $dados['estado'] = 'emitido';
            $parecer = Parecer::create($dados);
            session()->flash('status', "Parecer '{$parecer->titulo}' criado com sucesso.");
        }

        return redirect()->route('municipio.operacoes.pareceres', $this->municipio);
    }

    public function render()
    {
        return view('livewire.parecer-form');
    }
}
