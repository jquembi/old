<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\OutroProjecto;
use App\Services\InstrumentoDocumentoExtractorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class OutroProjetoForm extends Component
{
    use WithFileUploads;

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
    public ?string $orcamento = null;
    public ?string $observacoes = null;

    // Documento para leitura OCR / extração automática de metadados
    public mixed $documentoAnalise = null;
    public ?array $dadosExtraidos = null;
    public bool $emExtracao = false;

    public function mount(Municipio $municipio, mixed $projecto = null): void
    {
        $this->municipio = $municipio;

        $user = Auth::user();
        $permissaoNecessaria = $projecto ? 'outros_projectos.editar' : 'outros_projectos.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('outros_projectos.ver') && !$user->temPermissao('instrumentos.criar') && !$user->temPermissao('instrumentos.editar') && !$user->temPermissao('requalificacao.ver'))) {
            abort(403, 'Não tem permissão para aceder ao formulário de projectos.');
        }

        if ($projecto instanceof OutroProjecto) {
            $this->projecto = $projecto;
            $this->carregarProjecto($projecto);
        } elseif ($projecto && is_numeric($projecto)) {
            $this->projecto = OutroProjecto::where('municipio_id', $municipio->id)->findOrFail((int) $projecto);
            $this->carregarProjecto($this->projecto);
        } else {
            $this->entidade_responsavel = 'Administração Municipal de ' . $municipio->nome;
            $this->data_inicio = Carbon::now()->format('Y-m-d');
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
        $this->orcamento = $projecto->orcamento !== null ? (string) $projecto->orcamento : null;
        $this->observacoes = $projecto->observacoes;
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

            // Preenchimento automático de campos vazios
            if (empty($this->titulo) && !empty($resultado['titulo_sugerido'])) {
                $this->titulo = $resultado['titulo_sugerido'];
            }

            if (empty($this->descricao) && !empty($resultado['resumo_extraido'])) {
                $this->descricao = $resultado['resumo_extraido'];
            }

            if (in_array($resultado['estado_sugerido'] ?? null, ['planificado', 'em_curso', 'concluido', 'suspenso'], true)) {
                $this->estado = $resultado['estado_sugerido'];
            }

            $this->dispatch('notificar', [
                'tipo' => 'success',
                'mensagem' => 'Leitura OCR do documento concluída com sucesso.',
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

    public function definirAtalhoData(string $tipoAtalho): void
    {
        $hoje = Carbon::now();
        switch ($tipoAtalho) {
            case 'inicio_hoje':
                $this->data_inicio = $hoje->format('Y-m-d');
                break;
            case 'fim_6_meses':
                $inicio = $this->data_inicio ? Carbon::parse($this->data_inicio) : $hoje;
                $this->data_previsao_fim = $inicio->copy()->addMonths(6)->format('Y-m-d');
                break;
            case 'fim_1_ano':
                $inicio = $this->data_inicio ? Carbon::parse($this->data_inicio) : $hoje;
                $this->data_previsao_fim = $inicio->copy()->addYear()->format('Y-m-d');
                break;
            case 'fim_2_anos':
                $inicio = $this->data_inicio ? Carbon::parse($this->data_inicio) : $hoje;
                $this->data_previsao_fim = $inicio->copy()->addYears(2)->format('Y-m-d');
                break;
        }
    }

    public function definirFase(string $novaFase): void
    {
        $this->fase = $novaFase;
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
            'orcamento' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do projecto é obrigatório.',
            'tipo.required' => 'O tipo de projecto é obrigatório.',
            'estado.required' => 'O estado do projecto é obrigatório.',
            'data_previsao_fim.after_or_equal' => 'A data de previsão de conclusão deve ser posterior ou igual à data de início.',
            'orcamento.numeric' => 'O orçamento deve ser um valor numérico válido.',
        ];
    }

    public function guardar()
    {
        $user = Auth::user();
        $permissaoNecessaria = $this->registoId ? 'outros_projectos.editar' : 'outros_projectos.criar';
        if (!$user || (!$user->temPermissao($permissaoNecessaria) && !$user->temPermissao('instrumentos.editar') && !$user->temPermissao('instrumentos.criar') && !$user->temPermissao('requalificacao.ver'))) {
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
            'orcamento' => !empty($validated['orcamento']) ? (float) $validated['orcamento'] : null,
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
