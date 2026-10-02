<?php

namespace App\Livewire;

use App\Models\Loteamento;
use App\Models\LoteamentoFicheiro;
use App\Models\Municipio;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class LoteamentoForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public ?int $registoId = null;
    public ?Loteamento $loteamento = null;

    public string $nome = '';
    public ?string $descricao = null;
    public string $estado = 'em_curso';
    public ?string $localizacao = null;
    public array $ficheiros = [];
    public bool $isUploading = false;

    public function mount(Municipio $municipio, mixed $loteamento = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $loteamento ? 'operacoes.editar' : 'operacoes.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('operacoes.ver'))) {
            abort(403, 'Não tem permissão para aceder ao formulário de loteamentos.');
        }

        if ($loteamento instanceof Loteamento) {
            $this->loteamento = $loteamento;
            $this->carregarLoteamento($loteamento);
        } elseif ($loteamento && is_numeric($loteamento)) {
            $this->loteamento = Loteamento::where('municipio_id', $municipio->id)->findOrFail((int) $loteamento);
            $this->carregarLoteamento($this->loteamento);
        }
    }

    protected function carregarLoteamento(Loteamento $loteamento): void
    {
        $this->registoId = $loteamento->id;
        $this->nome = $loteamento->nome;
        $this->descricao = $loteamento->descricao;
        $this->estado = $loteamento->estado ?? 'em_curso';
        $this->localizacao = $loteamento->localizacao;
    }

    public function getUploadProgressProperty(): int
    {
        return count($this->ficheiros) > 0 ? 100 : 0;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'estado' => ['required', 'string', 'in:em_curso,aprovado,concluido'],
            'localizacao' => ['nullable', 'string', 'max:255'],
            'ficheiros.*' => ['nullable', 'file', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do loteamento é obrigatório.',
            'estado.required' => 'O estado do loteamento é obrigatório.',
            'ficheiros.*.max' => 'Cada ficheiro não pode exceder 50MB.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'operacoes.editar' : 'operacoes.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('operacoes.ver'))) {
            abort(403, 'Não tem permissão para gravar este loteamento.');
        }

        $validated = $this->validate();

        $dados = [
            'municipio_id' => $this->municipio->id,
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'estado' => $validated['estado'],
            'localizacao' => $validated['localizacao'] ?? null,
        ];

        if ($this->registoId) {
            $loteamento = Loteamento::where('municipio_id', $this->municipio->id)->findOrFail($this->registoId);
            $loteamento->update($dados);
            session()->flash('status', "Loteamento '{$loteamento->nome}' actualizado com sucesso.");
        } else {
            $dados['criado_por'] = Auth::id();
            $loteamento = Loteamento::create($dados);
            session()->flash('status', "Loteamento '{$loteamento->nome}' criado com sucesso.");
        }

        if (!empty($this->ficheiros)) {
            foreach ($this->ficheiros as $ficheiro) {
                $caminho = $ficheiro->store("loteamentos/{$this->municipio->id}", 'public');
                LoteamentoFicheiro::create([
                    'loteamento_id' => $loteamento->id,
                    'nome_original' => $ficheiro->getClientOriginalName(),
                    'caminho' => $caminho,
                    'tipo_ficheiro' => $ficheiro->getClientOriginalExtension(),
                    'mime_type' => $ficheiro->getMimeType(),
                    'tamanho' => $ficheiro->getSize(),
                    'enviado_por' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('municipio.loteamentos', $this->municipio);
    }

    public function render()
    {
        return view('livewire.loteamento-form');
    }
}
