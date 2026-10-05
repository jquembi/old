<?php

namespace App\Livewire;

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\InstrumentoFicheiro;
use App\Models\Municipio;
use App\Models\TipoInstrumento;
use App\Services\InstrumentoDocumentoExtractorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CartaMunicipalForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public TipoInstrumento $tipo;
    public ?int $registoId = null;

    // Campos do formulário
    public string $titulo = '';
    public ?string $descricao = null;
    public ?string $ano = null;
    public string $estado = 'rascunho';

    // Ficheiros
    public array $ficheiros = [];
    public array $ficheirosExistentes = [];
    public bool $isUploading = false;

    // Estado do OCR e Leitura de Documentos
    public ?array $ocrDados = null;
    public string $ocrTextoPreview = '';
    public string $ocrMensagem = '';
    public bool $ocrSucesso = false;
    public bool $mostrarTextoOcr = false;
    public bool $ocrProcessando = false;

    public function mount(Municipio $municipio, mixed $instrumento = null): void
    {
        $this->municipio = $municipio;
        $this->tipo = $this->tipoCarta();
        $this->registoId = $instrumento instanceof Instrumento ? $instrumento->id : ($instrumento ? (int) $instrumento : null);

        if ($this->registoId) {
            $registo = $this->cartaQuery()->with('ficheiros')->findOrFail($this->registoId);
            $this->titulo = $registo->titulo;
            $this->descricao = $registo->descricao;
            $this->ano = $registo->ano ?: (string) date('Y');
            $this->estado = $registo->estado;
            $this->ficheirosExistentes = $registo->ficheiros->map(fn ($f) => [
                'id' => $f->id,
                'nome_original' => $f->nome_original,
                'caminho' => $f->caminho,
                'tipo_ficheiro' => $f->tipo_ficheiro,
                'tamanho' => $f->tamanho,
                'created_at' => $f->created_at?->format('d/m/Y'),
            ])->toArray();
        } else {
            // Preenchimento automático inteligente inicial com o ano corrente
            $this->ano = (string) date('Y');
            $this->titulo = "Carta Municipal de {$this->municipio->nome} ({$this->ano})";
        }
    }

    public function getUploadProgressProperty(): int
    {
        return count($this->ficheiros) > 0 ? 100 : 0;
    }

    /**
     * Preenchimento automático do ano corrente com 1 clique.
     */
    public function preencherAnoAtual(): void
    {
        $this->ano = (string) date('Y');
        $this->dispatch('notificacao', [
            'tipo' => 'info',
            'mensagem' => "Ano preenchido automaticamente com {$this->ano}.",
        ]);
    }

    /**
     * Define um ano específico através dos atalhos rápidos.
     */
    public function definirAno(string $ano): void
    {
        $this->ano = $ano;
    }

    /**
     * Disparado quando novos ficheiros são anexados.
     */
    public function updatedFicheiros(): void
    {
        if (empty($this->ficheiros)) {
            return;
        }

        $primeiroFicheiro = $this->ficheiros[0] ?? null;

        if ($primeiroFicheiro instanceof TemporaryUploadedFile) {
            $this->executarLeituraOcrParaFicheiro($primeiroFicheiro);
        }
    }

    /**
     * Executa a extração OCR e análise inteligente da carta/mapa.
     */
    public function executarLeituraOcrParaFicheiro(TemporaryUploadedFile $ficheiro): void
    {
        $this->ocrProcessando = true;

        try {
            $extractor = app(InstrumentoDocumentoExtractorService::class);
            $caminho = $ficheiro->getRealPath();
            $nomeOriginal = $ficheiro->getClientOriginalName();

            $resultado = $extractor->extrairDeFicheiro(
                $caminho,
                $nomeOriginal,
                'Carta Municipal',
                $this->municipio->nome
            );

            if ($resultado['sucesso']) {
                $this->ocrSucesso = true;
                $this->ocrDados = $resultado['dados'];
                $this->ocrTextoPreview = $resultado['texto'];
                $this->ocrMensagem = $resultado['mensagem'];

                // Preenchimento automático dos campos
                if (! empty($this->ocrDados['ano'])) {
                    $this->ano = $this->ocrDados['ano'];
                }

                if (empty($this->titulo) || $this->titulo === "Carta Municipal de {$this->municipio->nome} (" . date('Y') . ')') {
                    if (! empty($this->ocrDados['titulo'])) {
                        $this->titulo = $this->ocrDados['titulo'];
                    }
                }

                if (! empty($this->ocrDados['estado']) && $this->estado === 'rascunho') {
                    $this->estado = $this->ocrDados['estado'];
                }

                if (empty($this->descricao) && ! empty($this->ocrDados['descricao'])) {
                    $this->descricao = $this->ocrDados['descricao'];
                }

                $this->dispatch('notificacao', [
                    'tipo' => 'success',
                    'mensagem' => "Leitura da carta concluída com sucesso! Ano detectado: {$this->ano}.",
                ]);
            } else {
                $this->ocrSucesso = false;
                $this->ocrMensagem = $resultado['mensagem'] ?: 'Não foi possível extrair texto estruturado desta carta.';
                $this->ocrTextoPreview = $resultado['texto'];
            }
        } catch (\Throwable $e) {
            $this->ocrSucesso = false;
            $this->ocrMensagem = 'Erro ao processar ficheiro: ' . $e->getMessage();
        } finally {
            $this->ocrProcessando = false;
        }
    }

    /**
     * Executa leitura OCR manualmente sob comando do utilizador.
     */
    public function executarLeituraManual(): void
    {
        if (! empty($this->ficheiros) && $this->ficheiros[0] instanceof TemporaryUploadedFile) {
            $this->executarLeituraOcrParaFicheiro($this->ficheiros[0]);
            return;
        }

        // Se estiver em edição e tiver ficheiro existente PDF
        if (! empty($this->ficheirosExistentes)) {
            $primeiroPdf = collect($this->ficheirosExistentes)->first(fn ($f) => strtolower($f['tipo_ficheiro']) === 'pdf');
            if ($primeiroPdf && Storage::disk('public')->exists($primeiroPdf['caminho'])) {
                $caminhoCompleto = Storage::disk('public')->path($primeiroPdf['caminho']);
                $extractor = app(InstrumentoDocumentoExtractorService::class);
                $resultado = $extractor->extrairDeFicheiro(
                    $caminhoCompleto,
                    $primeiroPdf['nome_original'],
                    'Carta Municipal',
                    $this->municipio->nome
                );

                if ($resultado['sucesso']) {
                    $this->ocrSucesso = true;
                    $this->ocrDados = $resultado['dados'];
                    $this->ocrTextoPreview = $resultado['texto'];
                    $this->ocrMensagem = 'Leitura OCR concluída a partir do documento existente.';

                    if (! empty($this->ocrDados['ano'])) {
                        $this->ano = $this->ocrDados['ano'];
                    }

                    $this->dispatch('notificacao', [
                        'tipo' => 'success',
                        'mensagem' => "OCR da carta concluído! Ano detectado: {$this->ano}.",
                    ]);
                    return;
                }
            }
        }

        $this->dispatch('notificacao', [
            'tipo' => 'warning',
            'mensagem' => 'Por favor, anexe um documento PDF, imagem ou peça cartográfica para executar a leitura.',
        ]);
    }

    /**
     * Aplica todas as sugestões do OCR nos campos do formulário.
     */
    public function aplicarSugestoesOcr(): void
    {
        if (! $this->ocrDados) {
            return;
        }

        if (! empty($this->ocrDados['ano'])) {
            $this->ano = $this->ocrDados['ano'];
        }
        if (! empty($this->ocrDados['titulo'])) {
            $this->titulo = $this->ocrDados['titulo'];
        }
        if (! empty($this->ocrDados['estado'])) {
            $this->estado = $this->ocrDados['estado'];
        }
        if (! empty($this->ocrDados['descricao'])) {
            $this->descricao = $this->ocrDados['descricao'];
        }

        $this->dispatch('notificacao', [
            'tipo' => 'success',
            'mensagem' => 'Sugestões da leitura OCR aplicadas aos campos da carta.',
        ]);
    }

    /**
     * Aplica um campo individual identificado pelo OCR.
     */
    public function aplicarCampoOcr(string $campo): void
    {
        if (! isset($this->ocrDados[$campo])) {
            return;
        }

        $valor = $this->ocrDados[$campo];

        match ($campo) {
            'ano' => $this->ano = (string) $valor,
            'titulo' => $this->titulo = (string) $valor,
            'estado' => $this->estado = (string) $valor,
            'descricao' => $this->descricao = (string) $valor,
            default => null,
        };

        $this->dispatch('notificacao', [
            'tipo' => 'info',
            'mensagem' => "Campo " . ucfirst($campo) . " atualizado com sugestão do OCR.",
        ]);
    }

    /**
     * Remove um ficheiro recém-carregado antes de gravar.
     */
    public function removerFicheiroNovo(int $index): void
    {
        if (isset($this->ficheiros[$index])) {
            unset($this->ficheiros[$index]);
            $this->ficheiros = array_values($this->ficheiros);
        }
    }

    /**
     * Remove um ficheiro já existente no banco de dados.
     */
    public function removerFicheiroExistente(int $ficheiroId): void
    {
        $ficheiro = InstrumentoFicheiro::query()
            ->where('instrumento_id', $this->registoId)
            ->where('id', $ficheiroId)
            ->first();

        if ($ficheiro) {
            Storage::disk('public')->delete($ficheiro->caminho);
            $ficheiro->delete();

            $this->ficheirosExistentes = array_values(
                array_filter($this->ficheirosExistentes, fn ($f) => $f['id'] !== $ficheiroId)
            );

            $this->dispatch('notificacao', [
                'tipo' => 'success',
                'mensagem' => 'Ficheiro removido com sucesso.',
            ]);
        }
    }

    public function toggleMostrarTextoOcr(): void
    {
        $this->mostrarTextoOcr = ! $this->mostrarTextoOcr;
    }

    public function limparOcr(): void
    {
        $this->ocrDados = null;
        $this->ocrTextoPreview = '';
        $this->ocrMensagem = '';
        $this->ocrSucesso = false;
        $this->mostrarTextoOcr = false;
    }

    public function guardar()
    {
        abort_unless(Auth::user()?->temPermissao($this->registoId ? 'cartas_municipais.editar' : 'cartas_municipais.criar'), 403);

        $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'ano' => ['nullable', 'digits:4'],
            'estado' => ['required', 'in:rascunho,submetido,aprovado,rejeitado'],
            'ficheiros.*' => ['file', 'max:51200'],
        ], [
            'titulo.required' => 'O título da carta municipal é obrigatório.',
            'ano.digits' => 'O ano deve conter exatamente 4 dígitos numéricos (ex: ' . date('Y') . ').',
            'estado.required' => 'Selecione o estado da carta.',
        ]);

        $registo = $this->registoId
            ? $this->cartaQuery()->findOrFail($this->registoId)
            : new Instrumento([
                'municipio_id' => $this->municipio->id,
                'tipo_instrumento_id' => $this->tipo->id,
                'criado_por' => Auth::id(),
            ]);

        $registo->fill([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'ano' => $this->ano ?: (string) date('Y'),
            'estado' => $this->estado,
            'actualizado_por' => Auth::id(),
        ])->save();

        foreach ($this->ficheiros as $ficheiro) {
            if (! $ficheiro instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = $ficheiro->store("cartas-municipais/{$registo->id}", 'public');

            InstrumentoFicheiro::create([
                'instrumento_id' => $registo->id,
                'nome_original' => $ficheiro->getClientOriginalName(),
                'caminho' => $path,
                'tipo_ficheiro' => strtolower($ficheiro->getClientOriginalExtension() ?: 'ficheiro'),
                'mime_type' => $ficheiro->getMimeType() ?: 'application/octet-stream',
                'tamanho' => $ficheiro->getSize() ?: 0,
                'enviado_por' => Auth::id(),
            ]);
        }

        return redirect()->route('municipio.cartas', $this->municipio);
    }

    public function render()
    {
        return view('livewire.carta-municipal-form', [
            'municipio' => $this->municipio,
            'registoId' => $this->registoId,
            'isUploading' => $this->isUploading,
            'uploadProgress' => $this->uploadProgress,
            'ficheiros' => $this->ficheiros,
            'ficheirosExistentes' => $this->ficheirosExistentes,
        ]);
    }

    private function cartaQuery()
    {
        return Instrumento::query()
            ->where('municipio_id', $this->municipio->id)
            ->where('tipo_instrumento_id', $this->tipo->id);
    }

    private function tipoCarta(): TipoInstrumento
    {
        $categoria = CategoriaInstrumento::query()
            ->where('slug', 'cartas-municipais')
            ->firstOrFail();

        return TipoInstrumento::query()
            ->where('categoria_id', $categoria->id)
            ->orderBy('ordem')
            ->firstOrFail();
    }
}
