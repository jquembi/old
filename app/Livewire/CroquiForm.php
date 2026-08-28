<?php

namespace App\Livewire;

use App\Models\Croqui;
use App\Models\CroquiProprietario;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Services\CroquiPdfDataExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CroquiForm extends Component
{
    use WithFileUploads;

    public Municipio $municipio;
    public ?int $registoId = null;
    public ?Croqui $croqui = null;

    // Basic fields
    public string $titulo = '';
    public ?string $ano = '';
    public string $visibilidade = 'privado';

    // File uploads
    public $ficheiro_pdf;
    public $memoria_descritiva;
    public $micro_localizacao;

    // Current file paths (for editing)
    public ?string $ficheiroPdfAtualCaminho = null;
    public ?string $ficheiroPdfAtualNome = null;
    public ?string $memoriaDescritivaAtualCaminho = null;
    public ?string $memoriaDescritivaAtualNome = null;
    public ?string $microAtualCaminho = null;

    // Legend data
    public ?string $area = '';
    public ?string $perimetro = '';
    public ?string $dimensao = '';
    public string $escala = '1/500';
    public ?string $tecnico_responsavel = '';
    public ?string $folha_numero = '';
    public ?string $data_folha = '';
    public string $finalidade = 'A fim de legalizar';

    // Proprietários
    public array $proprietarios = [];

    // Coordenadas
    public string $utm_zone = '33';
    public string $utm_hemisphere = 'S';
    public array $coordenadas = [];

    // PDF extraction feedback
    public ?string $mensagemExtracaoPdf = null;
    public bool $pdfPreenchidoAutomaticamente = false;
    public ?string $mensagemExtracaoMemoria = null;
    public bool $memoriaPreenchidaAutomaticamente = false;

    public int $uploadProgress = 0;

    public function mount(Municipio $municipio, ?int $croqui = null)
    {
        $this->municipio = $municipio;
        $this->registoId = $croqui;
        $this->ano = date('Y');
        $this->tecnico_responsavel = Auth::user()->name;

        if ($croqui) {
            $this->croqui = Croqui::with('proprietarios')->findOrFail($croqui);
            $this->loadCroquiData();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function initializeDefaults()
    {
        $this->proprietarios = [['nome' => '', 'bi' => '', 'contacto' => '']];
        $this->coordenadas = [
            ['lat_gms' => '', 'lat_utm' => '', 'lon_gms' => '', 'lon_utm' => '']
        ];
    }

    protected function loadCroquiData()
    {
        $this->titulo = $this->croqui->titulo ?? '';
        $this->ano = $this->croqui->ano ?? '';
        $this->visibilidade = $this->croqui->visibilidade ?? 'privado';
        $this->ficheiroPdfAtualCaminho = $this->croqui->ficheiro_pdf_caminho;
        $this->ficheiroPdfAtualNome = $this->croqui->ficheiro_pdf_nome;
        $this->memoriaDescritivaAtualCaminho = $this->croqui->memoria_descritiva_caminho;
        $this->memoriaDescritivaAtualNome = $this->croqui->memoria_descritiva_nome;
        $this->microAtualCaminho = $this->croqui->micro_localizacao_caminho;

        $this->area = $this->croqui->area ?? '';
        $this->perimetro = $this->croqui->perimetro ?? '';
        $this->dimensao = $this->croqui->dimensao ?? '';
        $this->escala = $this->croqui->escala ?? '1/500';
        $this->tecnico_responsavel = $this->croqui->tecnico_responsavel ?? '';
        $this->folha_numero = $this->croqui->folha_numero ?? '';
        $this->data_folha = $this->croqui->data_folha ?? '';
        $this->finalidade = $this->croqui->finalidade ?? 'A fim de legalizar';

        $this->utm_zone = $this->croqui->utm_zone ?? '33';
        $this->utm_hemisphere = $this->croqui->utm_hemisphere ?? 'S';

        // Load proprietários
        if ($this->croqui->proprietarios->isNotEmpty()) {
            $this->proprietarios = $this->croqui->proprietarios->map(function ($prop) {
                return [
                    'nome' => $prop->nome,
                    'bi' => $prop->bi ?? '',
                    'contacto' => $prop->contacto ?? '',
                ];
            })->toArray();
        } else {
            $this->proprietarios = [['nome' => '', 'bi' => '', 'contacto' => '']];
        }

        // Load coordenadas
        if ($this->croqui->coordenadas_texto) {
            $this->coordenadas = json_decode($this->croqui->coordenadas_texto, true) ?? [];
        } else {
            $this->coordenadas = [
                ['lat_gms' => '', 'lat_utm' => '', 'lon_gms' => '', 'lon_utm' => '']
            ];
        }
    }

    public function adicionarProprietario()
    {
        $this->proprietarios[] = ['nome' => '', 'bi' => '', 'contacto' => ''];
    }

    public function removerProprietario(int $index)
    {
        unset($this->proprietarios[$index]);
        $this->proprietarios = array_values($this->proprietarios);
    }

    public function adicionarCoordenada()
    {
        $this->coordenadas[] = ['lat_gms' => '', 'lat_utm' => '', 'lon_gms' => '', 'lon_utm' => ''];
    }

    public function removerCoordenada(int $index)
    {
        unset($this->coordenadas[$index]);
        $this->coordenadas = array_values($this->coordenadas);
    }

    public function removerFicheiroPdfAtual()
    {
        if ($this->croqui && $this->croqui->ficheiro_pdf_caminho) {
            Storage::disk('local')->delete($this->croqui->ficheiro_pdf_caminho);
            $this->croqui->ficheiro_pdf_caminho = null;
            $this->croqui->ficheiro_pdf_nome = null;
            $this->croqui->save();
            $this->ficheiroPdfAtualCaminho = null;
            $this->ficheiroPdfAtualNome = null;
        }
    }

    public function removerMemoriaDescritivaAtual()
    {
        if ($this->croqui && $this->croqui->memoria_descritiva_caminho) {
            Storage::disk('local')->delete($this->croqui->memoria_descritiva_caminho);
            $this->croqui->memoria_descritiva_caminho = null;
            $this->croqui->memoria_descritiva_nome = null;
            $this->croqui->save();
            $this->memoriaDescritivaAtualCaminho = null;
            $this->memoriaDescritivaAtualNome = null;
        }
    }

    public function removerMicroLocalizacaoAtual()
    {
        if ($this->croqui && $this->croqui->micro_localizacao_caminho) {
            Storage::disk('local')->delete($this->croqui->micro_localizacao_caminho);
            $this->croqui->micro_localizacao_caminho = null;
            $this->croqui->micro_localizacao_nome = null;
            $this->croqui->save();
            $this->microAtualCaminho = null;
        }
    }

    public function updatedFicheiroPdf(): void
    {
        $this->validateOnly('ficheiro_pdf', $this->rules(), $this->messages());
        $this->validateUploadedDocument('ficheiro_pdf', 'Arquivo do croqui');
    }

    public function updatedMemoriaDescritiva(): void
    {
        $this->validateOnly('memoria_descritiva', $this->rules(), $this->messages());
        $this->validateUploadedDocument('memoria_descritiva', 'Memoria descritiva');

        // A extração será feita via JavaScript
        // Este método apenas valida o ficheiro
    }

    public function processarDadosExtraidosMemoria(array $dados): void
    {
        // Preencher campos com dados extraídos pelo JavaScript da memória descritiva
        if (!empty($dados['titulo'])) {
            $this->titulo = $dados['titulo'];
        }
        if (!empty($dados['area'])) {
            $this->area = $dados['area'];
        }
        if (!empty($dados['perimetro'])) {
            $this->perimetro = $dados['perimetro'];
        }
        if (!empty($dados['dimensao'])) {
            $this->dimensao = $dados['dimensao'];
        }
        if (!empty($dados['escala'])) {
            $this->escala = $dados['escala'];
        }
        if (!empty($dados['tecnico_responsavel'])) {
            $this->tecnico_responsavel = $dados['tecnico_responsavel'];
        }
        if (!empty($dados['data_folha'])) {
            $this->data_folha = $dados['data_folha'];
        }
        if (!empty($dados['ano'])) {
            $this->ano = $dados['ano'];
        }

        // Preencher proprietários
        if (!empty($dados['proprietarios'][0]['nome'])) {
            $this->proprietarios = $dados['proprietarios'];
        }

        // Preencher coordenadas
        if (!empty($dados['coordenadas']) && !empty($dados['coordenadas'][0]['lat_gms'])) {
            $this->coordenadas = $dados['coordenadas'];
        }

        $this->dispatch('toast', type: 'success', message: 'Dados extraídos com sucesso do documento.');
    }

    public function processarDadosExtraidosPdf(array $dados): void
    {
        // Preencher campos com dados extraídos pelo JavaScript do ficheiro PDF
        if (!empty($dados['titulo'])) {
            $this->titulo = $dados['titulo'];
        }
        if (!empty($dados['area'])) {
            $this->area = $dados['area'];
        }
        if (!empty($dados['perimetro'])) {
            $this->perimetro = $dados['perimetro'];
        }
        if (!empty($dados['dimensao'])) {
            $this->dimensao = $dados['dimensao'];
        }
        if (!empty($dados['escala'])) {
            $this->escala = $dados['escala'];
        }
        if (!empty($dados['tecnico_responsavel'])) {
            $this->tecnico_responsavel = $dados['tecnico_responsavel'];
        }
        if (!empty($dados['data_folha'])) {
            $this->data_folha = $dados['data_folha'];
        }
        if (!empty($dados['ano'])) {
            $this->ano = $dados['ano'];
        }

        // Preencher proprietários
        if (!empty($dados['proprietarios'][0]['nome'])) {
            $this->proprietarios = $dados['proprietarios'];
        }

        // Preencher coordenadas
        if (!empty($dados['coordenadas']) && !empty($dados['coordenadas'][0]['lat_gms'])) {
            $this->coordenadas = $dados['coordenadas'];
        }

        $this->dispatch('toast', type: 'success', message: 'Dados extraídos com sucesso do documento.');
    }

    public function erroExtracaoMemoria(string $mensagem): void
    {
        $this->dispatch('toast', type: 'error', message: 'Erro ao extrair dados: ' . $mensagem);
    }

    public function erroExtracaoPdf(string $mensagem): void
    {
        $this->dispatch('toast', type: 'error', message: 'Erro ao extrair dados: ' . $mensagem);
    }

    public function processarDadosExtraidos(array $dados): void
    {
        // Método legado para compatibilidade
        $this->processarDadosExtraidosMemoria($dados);
    }

    public function erroExtracao(string $mensagem): void
    {
        // Método legado para compatibilidade
        $this->erroExtracaoMemoria($mensagem);
    }

    public function guardar()
    {
        $this->withValidator(function (Validator $validator) {
            $validator->after(function (Validator $validator) {
                $this->validateDocumentWithValidator($validator, 'ficheiro_pdf', 'Arquivo do croqui');
                $this->validateDocumentWithValidator($validator, 'memoria_descritiva', 'Memoria descritiva');
            });
        })->validate($this->rules(), $this->messages());

        $operacao = OperacaoUrbanistica::firstOrCreate(
            [
                'municipio_id' => $this->municipio->id,
                'titulo' => 'Croquis Elaborados',
            ],
            [
                'descricao' => 'Operacao base para croquis elaborados',
                'criado_por' => Auth::id(),
            ]
        );

        $data = [
            'operacao_id' => $operacao->id,
            'tipo' => 'elaborado',
            'titulo' => $this->titulo,
            'slug' => \Illuminate\Support\Str::slug($this->titulo),
            'ano' => $this->ano,
            'visibilidade' => $this->visibilidade,
            'area' => $this->area,
            'perimetro' => $this->perimetro,
            'dimensao' => $this->dimensao,
            'escala' => $this->escala,
            'tecnico_responsavel' => $this->tecnico_responsavel,
            'folha_numero' => $this->folha_numero,
            'data_folha' => $this->data_folha,
            'finalidade' => $this->finalidade,
            'utm_zone' => $this->utm_zone,
            'utm_hemisphere' => $this->utm_hemisphere,
            'coordenadas_texto' => json_encode($this->coordenadas),
            'criado_por' => auth()->id(),
        ];

        // Handle PDF upload
        if ($this->ficheiro_pdf) {
            $path = $this->ficheiro_pdf->store("operacoes/{$operacao->id}/croquis", 'local');
            $data['ficheiro_pdf_caminho'] = $path;
            $data['ficheiro_pdf_nome'] = $this->ficheiro_pdf->getClientOriginalName();
        } elseif ($this->croqui && $this->croqui->ficheiro_pdf_caminho) {
            $data['ficheiro_pdf_caminho'] = $this->croqui->ficheiro_pdf_caminho;
            $data['ficheiro_pdf_nome'] = $this->croqui->ficheiro_pdf_nome;
        }

        // Handle memoria descritiva upload
        if ($this->memoria_descritiva) {
            $path = $this->memoria_descritiva->store("operacoes/{$operacao->id}/croquis/memorias", 'local');
            $data['memoria_descritiva_caminho'] = $path;
            $data['memoria_descritiva_nome'] = $this->memoria_descritiva->getClientOriginalName();
        } elseif ($this->croqui && $this->croqui->memoria_descritiva_caminho) {
            $data['memoria_descritiva_caminho'] = $this->croqui->memoria_descritiva_caminho;
            $data['memoria_descritiva_nome'] = $this->croqui->memoria_descritiva_nome;
        }

        // Handle micro localização upload
        if ($this->micro_localizacao) {
            $path = $this->micro_localizacao->store("operacoes/{$operacao->id}/croquis/micro", 'local');
            $data['micro_localizacao_caminho'] = $path;
            $data['micro_localizacao_nome'] = $this->micro_localizacao->getClientOriginalName();
        } elseif ($this->croqui && $this->croqui->micro_localizacao_caminho) {
            $data['micro_localizacao_caminho'] = $this->croqui->micro_localizacao_caminho;
            $data['micro_localizacao_nome'] = $this->croqui->micro_localizacao_nome;
        }

        if ($this->croqui) {
            $this->croqui->update($data);
            $this->croqui->proprietarios()->delete();
        } else {
            $this->croqui = Croqui::create($data);
        }

        // Save proprietários
        foreach ($this->proprietarios as $prop) {
            if (!empty($prop['nome'])) {
                $this->croqui->proprietarios()->create([
                    'nome' => $prop['nome'],
                    'bi' => $prop['bi'] ?? null,
                    'contacto' => $prop['contacto'] ?? null,
                ]);
            }
        }

        $this->dispatch('toast', type: 'success', message: $this->registoId ? 'Croqui actualizado com sucesso!' : 'Croqui criado com sucesso!');
        $this->redirect(route('municipio.operacoes.croquis-elaborados', $this->municipio));
    }

    public function render()
    {
        return view('livewire.croqui-form', [
            'municipio' => $this->municipio,
            'registoId' => $this->registoId,
            'titulo' => $this->titulo,
            'ano' => $this->ano,
            'visibilidade' => $this->visibilidade,
            'ficheiro_pdf' => $this->ficheiro_pdf,
            'memoria_descritiva' => $this->memoria_descritiva,
            'micro_localizacao' => $this->micro_localizacao,
            'ficheiroPdfAtualCaminho' => $this->ficheiroPdfAtualCaminho,
            'ficheiroPdfAtualNome' => $this->ficheiroPdfAtualNome,
            'memoriaDescritivaAtualCaminho' => $this->memoriaDescritivaAtualCaminho,
            'memoriaDescritivaAtualNome' => $this->memoriaDescritivaAtualNome,
            'microAtualCaminho' => $this->microAtualCaminho,
            'area' => $this->area,
            'perimetro' => $this->perimetro,
            'dimensao' => $this->dimensao,
            'escala' => $this->escala,
            'tecnico_responsavel' => $this->tecnico_responsavel,
            'folha_numero' => $this->folha_numero,
            'data_folha' => $this->data_folha,
            'finalidade' => $this->finalidade,
            'proprietarios' => $this->proprietarios,
            'utm_zone' => $this->utm_zone,
            'utm_hemisphere' => $this->utm_hemisphere,
            'coordenadas' => $this->coordenadas,
            'mensagemExtracaoPdf' => $this->mensagemExtracaoPdf,
            'pdfPreenchidoAutomaticamente' => $this->pdfPreenchidoAutomaticamente,
            'mensagemExtracaoMemoria' => $this->mensagemExtracaoMemoria,
            'memoriaPreenchidaAutomaticamente' => $this->memoriaPreenchidaAutomaticamente,
            'uploadProgress' => $this->uploadProgress,
        ]);
    }

    protected function rules(): array
    {
        $ficheiroPdfObrigatorio = ! $this->croqui && ! $this->ficheiroPdfAtualCaminho ? 'required' : 'nullable';
        $memoriaObrigatoria = ! $this->croqui && ! $this->memoriaDescritivaAtualCaminho ? 'required' : 'nullable';

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'ano' => ['nullable', 'digits:4'],
            'visibilidade' => ['required', 'in:privado,publico'],
            'ficheiro_pdf' => [$ficheiroPdfObrigatorio, 'file', 'max:51200'],
            'memoria_descritiva' => [$memoriaObrigatoria, 'file', 'max:51200'],
            'micro_localizacao' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'area' => ['nullable', 'string'],
            'perimetro' => ['nullable', 'string'],
            'dimensao' => ['nullable', 'string'],
            'escala' => ['required', 'string'],
            'tecnico_responsavel' => ['nullable', 'string', 'max:255'],
            'folha_numero' => ['nullable', 'string', 'max:50'],
            'data_folha' => ['nullable', 'string', 'max:7'],
            'finalidade' => ['required', 'string'],
            'proprietarios' => ['required', 'array', 'min:1'],
            'proprietarios.*.nome' => ['required', 'string', 'max:255'],
            'coordenadas' => ['required', 'array', 'min:1'],
            'utm_zone' => ['required', 'in:32,33,34'],
            'utm_hemisphere' => ['required', 'in:N,S'],
        ];
    }

    protected function messages(): array
    {
        return [
            'ficheiro_pdf.required' => 'Carregue o arquivo do croqui em PDF ou Word.',
            'ficheiro_pdf.file' => 'O arquivo do croqui deve ser um ficheiro valido.',
            'ficheiro_pdf.uploaded' => 'Nao foi possivel carregar o arquivo do croqui. Confirme que e PDF, DOC ou DOCX e que nao excede 50 MB.',
            'ficheiro_pdf.max' => 'O arquivo do croqui nao pode exceder 50 MB.',
            'memoria_descritiva.required' => 'Carregue a memoria descritiva em PDF ou Word.',
            'memoria_descritiva.file' => 'A memoria descritiva deve ser um ficheiro valido.',
            'memoria_descritiva.uploaded' => 'Nao foi possivel carregar a memoria descritiva. Confirme que e PDF, DOC ou DOCX e que nao excede 50 MB.',
            'memoria_descritiva.max' => 'A memoria descritiva nao pode exceder 50 MB.',
            'micro_localizacao.image' => 'A micro-localizacao deve ser uma imagem valida.',
            'micro_localizacao.uploaded' => 'Nao foi possivel carregar a micro-localizacao. Confirme que e uma imagem valida e que nao excede 10 MB.',
            'micro_localizacao.mimes' => 'A micro-localizacao deve estar em JPG, PNG ou WebP.',
            'ano.digits' => 'O ano deve ter 4 digitos.',
            'utm_zone.in' => 'A zona UTM deve ser 32, 33 ou 34.',
            'utm_hemisphere.in' => 'O hemisferio UTM deve ser N ou S.',
        ];
    }

    private function validateUploadedDocument(string $property, string $label): void
    {
        $file = $this->{$property};

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $validator = validator([], []);
        $this->validateDocumentWithValidator($validator, $property, $label);

        foreach ($validator->errors()->get($property) as $message) {
            $this->addError($property, $message);
        }
    }

    private function validateDocumentWithValidator(Validator $validator, string $property, string $label): void
    {
        $file = $this->{$property};

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (! in_array($extension, $allowedExtensions, true)) {
            $validator->errors()->add($property, "{$label} deve estar em PDF, DOC ou DOCX.");
        }

        if (! in_array($mime, $allowedMimes, true)) {
            $validator->errors()->add($property, "{$label} nao parece ser um PDF ou documento Word valido.");
        }

        if (($file->getSize() ?: 0) <= 0) {
            $validator->errors()->add($property, "{$label} esta vazio ou corrompido.");
        }
    }
}
