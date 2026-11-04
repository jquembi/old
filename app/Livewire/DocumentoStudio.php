<?php

namespace App\Livewire;

use App\Models\DocumentoLayout;
use App\Services\PdfQrStampService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Document Studio')]
class DocumentoStudio extends Component
{
    public string $tipoSelecionado = 'croqui';

    public string $orientacao = 'retrato';

    public bool $ativo = true;

    public bool $personalizado = false;

    /** @var array<int, array<string, mixed>> */
    public array $blocos = [];

    /** @var array<string, string> */
    public array $institucional = [];

    public bool $mostrarInstitucional = false;

    public function mount(?string $tipo = null): void
    {
        $this->institucional = DocumentoLayout::institucional();
        $this->carregar($tipo && isset(DocumentoLayout::tipos()[$tipo]) ? $tipo : 'croqui');
    }

    public function selecionarTipo(string $tipo): void
    {
        if (isset(DocumentoLayout::tipos()[$tipo])) {
            $this->carregar($tipo);
        }
    }

    protected function carregar(string $tipo): void
    {
        $layout = DocumentoLayout::efetivo($tipo);
        $this->tipoSelecionado = $tipo;
        $this->orientacao = $layout['orientacao'];
        $this->ativo = $layout['ativo'];
        $this->personalizado = $layout['personalizado'];
        $this->blocos = $layout['blocos'];
    }

    public function guardar(array $blocos, string $orientacao, bool $ativo): void
    {
        $this->authorizeAcesso();

        DocumentoLayout::updateOrCreate(
            ['tipo_documento' => $this->tipoSelecionado],
            [
                'nome' => DocumentoLayout::tipos()[$this->tipoSelecionado]['nome'] ?? $this->tipoSelecionado,
                'formato_papel' => 'A4',
                'orientacao' => $orientacao === 'paisagem' ? 'paisagem' : 'retrato',
                'blocos' => $this->sanear($blocos),
                'ativo' => $ativo,
                'atualizado_por' => Auth::id(),
            ]
        );

        $this->carregar($this->tipoSelecionado);
        $this->dispatch('studio-guardado');
    }

    public function repor(): void
    {
        $this->authorizeAcesso();
        DocumentoLayout::where('tipo_documento', $this->tipoSelecionado)->delete();
        $this->carregar($this->tipoSelecionado);
        $this->dispatch('studio-reposto', blocos: $this->blocos, orientacao: $this->orientacao);
    }

    public function guardarInstitucional(): void
    {
        $this->authorizeAcesso();
        $dados = $this->validate([
            'institucional.orgao' => ['required', 'string', 'max:120'],
            'institucional.subtitulo' => ['nullable', 'string', 'max:200'],
            'institucional.morada' => ['nullable', 'string', 'max:200'],
            'institucional.contactos' => ['nullable', 'string', 'max:200'],
        ])['institucional'];

        DocumentoLayout::guardarInstitucional($dados, Auth::id());
        $this->institucional = DocumentoLayout::institucional();
        $this->mostrarInstitucional = false;
        $this->dispatch('studio-institucional', institucional: $this->institucional);
    }

    public function previsualizar(array $blocos, string $orientacao): void
    {
        try {
            $pdf = PdfQrStampService::gerarExemplo(
                ['orientacao' => $orientacao, 'blocos' => $this->sanear($blocos)],
                DocumentoLayout::tipos()[$this->tipoSelecionado]['nome'] ?? ''
            );
            $this->dispatch('studio-preview', pdf: base64_encode($pdf));
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('studio-erro', mensagem: 'Não foi possível gerar a pré-visualização.');
        }
    }

    protected function authorizeAcesso(): void
    {
        abort_unless(Auth::check(), 403);
    }

    /** Garante que apenas blocos e propriedades válidas são persistidos. */
    protected function sanear(array $blocos): array
    {
        $limpos = [];
        foreach (array_slice($blocos, 0, 60) as $b) {
            if (! is_array($b) || ! in_array($b['tipo'] ?? '', DocumentoLayout::TIPOS_BLOCO, true)) {
                continue;
            }
            $props = is_array($b['props'] ?? null) ? $b['props'] : [];
            $limpos[] = [
                'id' => substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($b['id'] ?? uniqid('b'))), 0, 40) ?: uniqid('b'),
                'tipo' => $b['tipo'],
                'x' => max(0, min(100, round((float) ($b['x'] ?? 0), 2))),
                'y' => max(0, min(100, round((float) ($b['y'] ?? 0), 2))),
                'w' => max(0.5, min(100, round((float) ($b['w'] ?? 10), 2))),
                'h' => max(0.1, min(100, round((float) ($b['h'] ?? 5), 2))),
                'pagina' => in_array($b['pagina'] ?? 'todas', ['todas', 'primeira', 'ultima', 'exceto_primeira', 'impares', 'pares'], true) ? ($b['pagina'] ?? 'todas') : 'todas',
                'bloqueado' => (bool) ($b['bloqueado'] ?? false),
                'oculto' => (bool) ($b['oculto'] ?? false),
                'props' => array_map(
                    fn ($v) => is_string($v) ? mb_substr($v, 0, 300) : (is_scalar($v) ? $v : null),
                    array_intersect_key($props, array_flip([
                        'texto', 'tamanho', 'negrito', 'italico', 'alinhamento', 'cor', 'fonte', 'cor_fundo', 'cor_borda',
                        'espessura', 'angulo', 'moldura', 'cor_moldura', 'rotulo', 'nivel', 'quadrado',
                    ]))
                ),
            ];
        }

        return $limpos;
    }

    public function render()
    {
        return view('livewire.documento-studio', [
            'tipos' => DocumentoLayout::tipos(),
            'modelos' => DocumentoLayout::modelos(),
            'logo' => asset($this->institucional['logo'] ?? DocumentoLayout::institucionalPadrao()['logo']),
        ]);
    }
}
