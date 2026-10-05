<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\ProjectoSona;
use App\Models\ProjectoSonaFicheiro;
use App\Services\InstrumentoDocumentoExtractorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    // Ficheiros já associados ao projecto (quando em edição)
    public array $ficheirosExistentes = [];

    // Documento auxiliar para leitura OCR / extração automática de metadados
    public mixed $documentoAnalise = null;
    public ?array $dadosExtraidos = null;
    public bool $emExtracao = false;

    public function mount(Municipio $municipio, mixed $projecto = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $projecto ? 'sona.editar' : 'sona.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('sona.editar_municipio') && !$user->temPermissao('sona.ver') && !$user->temPermissao('instrumentos.criar'))) {
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

        $this->carregarFicheirosExistentes();
    }

    protected function carregarFicheirosExistentes(): void
    {
        if ($this->projecto) {
            $this->ficheirosExistentes = $this->projecto->ficheiros()->get()->toArray();
        }
    }

    public function updatedDocumentoAnalise(): void
    {
        $this->validate([
            'documentoAnalise' => 'file|mimes:pdf,docx,jpg,jpeg,png|max:51200',
        ], [
            'documentoAnalise.mimes' => 'O ficheiro para análise deve ser um PDF, DOCX ou imagem (JPG, PNG).',
            'documentoAnalise.max' => 'O ficheiro para análise não pode exceder 50MB.',
        ]);

        $this->analisarDocumento();
    }

    public function analisarDocumento(): void
    {
        if (!$this->documentoAnalise) {
            return;
        }

        $this->emExtracao = true;

        try {
            $caminhoTemp = $this->documentoAnalise->getRealPath();
            $nomeOriginal = $this->documentoAnalise->getClientOriginalName();
            $mimeType = $this->documentoAnalise->getMimeType();

            $service = app(InstrumentoDocumentoExtractorService::class);
            $resultado = $service->extrairMetadados($caminhoTemp, $nomeOriginal, $mimeType);

            $this->dadosExtraidos = $resultado;

            // Se o título estiver vazio, preenche automaticamente
            if (empty($this->titulo) && !empty($resultado['titulo_sugerido'])) {
                $this->titulo = $resultado['titulo_sugerido'];
            }

            // Se a descrição estiver vazia, preenche com o resumo extraído
            if (empty($this->descricao) && !empty($resultado['resumo_extraido'])) {
                $this->descricao = $resultado['resumo_extraido'];
            }

            // Se detectou estado
            if (!empty($resultado['estado_sugerido'])) {
                $this->estado = $resultado['estado_sugerido'];
            }

            $this->dispatch('notificar', [
                'tipo' => 'success',
                'mensagem' => 'Leitura OCR / Análise do documento concluída com sucesso.',
            ]);
        } catch (\Throwable $e) {
            $this->dadosExtraidos = [
                'erro' => 'Não foi possível extrair os dados automaticamente: ' . $e->getMessage(),
            ];
        } finally {
            $this->emExtracao = false;
        }
    }

    public function aplicarSugestao(string $campo, mixed $valor): void
    {
        if (property_exists($this, $campo)) {
            $this->{$campo} = $valor;
        }
    }

    public function aplicarTodasSugestoes(): void
    {
        if (!$this->dadosExtraidos) {
            return;
        }

        if (!empty($this->dadosExtraidos['titulo_sugerido'])) {
            $this->titulo = $this->dadosExtraidos['titulo_sugerido'];
        }
        if (!empty($this->dadosExtraidos['resumo_extraido'])) {
            $this->descricao = $this->dadosExtraidos['resumo_extraido'];
        }
        if (!empty($this->dadosExtraidos['estado_sugerido'])) {
            $this->estado = $this->dadosExtraidos['estado_sugerido'];
        }
    }

    public function definirFase(string $novaFase): void
    {
        $this->fase = $novaFase;
    }

    public function removerFicheiroNovo(int $index): void
    {
        if (isset($this->ficheiros[$index])) {
            unset($this->ficheiros[$index]);
            $this->ficheiros = array_values($this->ficheiros);
        }
    }

    public function eliminarFicheiroExistente(int $ficheiroId): void
    {
        $user = Auth::user();
        if (!$user || (!$user->temPermissao('sona.editar') && !$user->temPermissao('sona.editar_municipio') && !$user->temPermissao('sona.eliminar') && !$user->temPermissao('instrumentos.editar'))) {
            $this->dispatch('notificar', [
                'tipo' => 'error',
                'mensagem' => 'Não tem permissão para remover ficheiros.',
            ]);
            return;
        }

        $ficheiro = ProjectoSonaFicheiro::findOrFail($ficheiroId);
        if ($ficheiro->caminho && Storage::disk('public')->exists($ficheiro->caminho)) {
            Storage::disk('public')->delete($ficheiro->caminho);
        }
        $ficheiro->delete();

        $this->carregarFicheirosExistentes();

        $this->dispatch('notificar', [
            'tipo' => 'success',
            'mensagem' => 'Ficheiro removido com sucesso.',
        ]);
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
            'tipo.required' => 'O tipo de projecto é obrigatório.',
            'estado.required' => 'O estado do projecto é obrigatório.',
            'ficheiros.*.max' => 'Cada ficheiro não pode exceder 50MB.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'sona.editar' : 'sona.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('sona.editar_municipio') && !$user->temPermissao('sona.ver') && !$user->temPermissao('instrumentos.criar'))) {
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
