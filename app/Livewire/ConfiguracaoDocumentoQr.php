<?php

namespace App\Livewire;

use App\Models\TipoDocumentoQrConfig;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Personalização de QR Code por Layout de Documento')]
class ConfiguracaoDocumentoQr extends Component
{
    public string $tipoSelecionado = 'croqui';

    // Propriedades do layout e posicionamento do QR
    public string $nome = '';
    public string $descricao = '';
    public string $formato_papel = 'A4';
    public string $orientacao = 'retrato';
    public string $pagina = 'todas';
    public float $posicao_x_pct = 88.0;
    public float $posicao_y_pct = 82.0;
    public float $largura_pct = 7.5;
    public float $tamanho_mm = 20.0;

    // Propriedades visuais do QR
    public string $cor_qr = '#18181b';
    public string $cor_fundo = '#ffffff';
    public bool $incluir_moldura = true;
    public string $cor_moldura = '#e4e4e7';
    public bool $incluir_rotulo = true;
    public string $texto_rotulo = 'AUTENTICIDADE • QR CODE';
    public string $nivel_correcao = 'M';
    public bool $ativo = true;

    // Estado da interface e modal de pré-visualização
    public string $zoomPreview = '100'; // 75, 100, 125
    public string $documentoModelo = 'modelo'; // modelo, esqueleto
    public bool $modalPrevisualizacaoAberta = false;
    public string $urlExemploPrevisualizacao = '';

    public function abrirModalPrevisualizacao(): void
    {
        $this->urlExemploPrevisualizacao = url('/documentos/verificar/EXEMPLO-' . strtoupper($this->tipoSelecionado) . '-GTERRAS-2026');
        $this->modalPrevisualizacaoAberta = true;
    }

    public function fecharModalPrevisualizacao(): void
    {
        $this->modalPrevisualizacaoAberta = false;
    }

    protected function rules(): array
    {
        return [
            'tipoSelecionado' => 'required|string',
            'nome' => 'required|string|max:120',
            'descricao' => 'nullable|string|max:500',
            'formato_papel' => 'required|in:A4,A3,personalizado',
            'orientacao' => 'required|in:retrato,paisagem',
            'pagina' => 'required|in:primeira,ultima,todas',
            'posicao_x_pct' => 'required|numeric|min:0|max:95',
            'posicao_y_pct' => 'required|numeric|min:0|max:95',
            'largura_pct' => 'required|numeric|min:3|max:30',
            'tamanho_mm' => 'required|numeric|min:10|max:60',
            'cor_qr' => 'required|string|max:20',
            'cor_fundo' => 'required|string|max:20',
            'incluir_moldura' => 'boolean',
            'cor_moldura' => 'required|string|max:20',
            'incluir_rotulo' => 'boolean',
            'texto_rotulo' => 'nullable|string|max:80',
            'nivel_correcao' => 'required|in:L,M,Q,H',
            'ativo' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->carregarConfiguracao($this->tipoSelecionado);
    }

    public function selecionarTipo(string $tipo): void
    {
        $this->tipoSelecionado = $tipo;
        $this->carregarConfiguracao($tipo);
        $this->dispatch('qr-tipo-alterado', [
            'tipo' => $tipo,
            'posicao_x' => $this->posicao_x_pct,
            'posicao_y' => $this->posicao_y_pct,
            'largura' => $this->largura_pct,
            'cor_qr' => $this->cor_qr,
            'cor_fundo' => $this->cor_fundo,
            'rotulo' => $this->texto_rotulo,
            'orientacao' => $this->orientacao,
        ]);
    }

    public function carregarConfiguracao(string $tipo): void
    {
        $config = TipoDocumentoQrConfig::obterParaTipo($tipo);

        $this->nome = $config->nome;
        $this->descricao = $config->descricao ?? '';
        $this->formato_papel = $config->formato_papel;
        $this->orientacao = $config->orientacao;
        $this->pagina = $config->pagina;
        $this->posicao_x_pct = (float) $config->posicao_x_pct;
        $this->posicao_y_pct = (float) $config->posicao_y_pct;
        $this->largura_pct = (float) $config->largura_pct;
        $this->tamanho_mm = (float) $config->tamanho_mm;
        $this->cor_qr = $config->cor_qr;
        $this->cor_fundo = $config->cor_fundo;
        $this->incluir_moldura = (bool) $config->incluir_moldura;
        $this->cor_moldura = $config->cor_moldura;
        $this->incluir_rotulo = (bool) $config->incluir_rotulo;
        $this->texto_rotulo = $config->texto_rotulo ?? 'AUTENTICIDADE • QR CODE';
        $this->nivel_correcao = $config->nivel_correcao;
        $this->ativo = (bool) $config->ativo;
    }

    /**
     * Atualiza as coordenadas enviadas interativamente via arrastar/soltar no preview.
     */
    public function atualizarPosicaoInterativa(float $x, float $y, ?float $largura = null): void
    {
        $this->posicao_x_pct = max(0.0, min(95.0, round($x, 2)));
        $this->posicao_y_pct = max(0.0, min(95.0, round($y, 2)));

        if ($largura !== null) {
            $this->largura_pct = max(3.0, min(30.0, round($largura, 2)));
        }
    }

    /**
     * Define posições de atalho (Cantos da página).
     */
    public function aplicarPosicaoPredefinida(string $posicao): void
    {
        switch ($posicao) {
            case 'superior_direito':
                $this->posicao_x_pct = 88.0;
                $this->posicao_y_pct = 4.0;
                break;
            case 'superior_esquerdo':
                $this->posicao_x_pct = 4.0;
                $this->posicao_y_pct = 4.0;
                break;
            case 'inferior_direito':
                $this->posicao_x_pct = 88.0;
                $this->posicao_y_pct = 84.0;
                break;
            case 'inferior_esquerdo':
                $this->posicao_x_pct = 4.0;
                $this->posicao_y_pct = 84.0;
                break;
            case 'inferior_centro':
                $this->posicao_x_pct = 46.0;
                $this->posicao_y_pct = 84.0;
                break;
        }

        $this->dispatch('qr-posicao-atualizada', [
            'x' => $this->posicao_x_pct,
            'y' => $this->posicao_y_pct,
        ]);
    }

    public function salvar(): void
    {
        $this->validate();

        $config = TipoDocumentoQrConfig::where('tipo_documento', $this->tipoSelecionado)->first();
        if (! $config) {
            $config = new TipoDocumentoQrConfig();
            $config->tipo_documento = $this->tipoSelecionado;
        }

        $config->fill([
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'formato_papel' => $this->formato_papel,
            'orientacao' => $this->orientacao,
            'pagina' => $this->pagina,
            'posicao_x_pct' => $this->posicao_x_pct,
            'posicao_y_pct' => $this->posicao_y_pct,
            'largura_pct' => $this->largura_pct,
            'tamanho_mm' => $this->tamanho_mm,
            'cor_qr' => $this->cor_qr,
            'cor_fundo' => $this->cor_fundo,
            'incluir_moldura' => $this->incluir_moldura,
            'cor_moldura' => $this->cor_moldura,
            'incluir_rotulo' => $this->incluir_rotulo,
            'texto_rotulo' => $this->texto_rotulo,
            'nivel_correcao' => $this->nivel_correcao,
            'ativo' => $this->ativo,
            'atualizado_por' => auth()->id(),
        ]);

        $config->save();

        $this->dispatch('toast', type: 'success', message: "Posição e estilo do QR para '{$this->nome}' gravados com sucesso! Todos os documentos desta natureza seguirão este layout.");
    }

    public function restaurarPadrao(): void
    {
        $predefinidos = TipoDocumentoQrConfig::tiposPredefinidos();
        if (isset($predefinidos[$this->tipoSelecionado])) {
            $dados = $predefinidos[$this->tipoSelecionado];

            $this->formato_papel = $dados['formato_papel'];
            $this->orientacao = $dados['orientacao'];
            $this->pagina = $dados['pagina'];
            $this->posicao_x_pct = (float) $dados['posicao_x_pct'];
            $this->posicao_y_pct = (float) $dados['posicao_y_pct'];
            $this->largura_pct = (float) $dados['largura_pct'];
            $this->tamanho_mm = (float) $dados['tamanho_mm'];
            $this->cor_qr = $dados['cor_qr'];
            $this->cor_fundo = $dados['cor_fundo'];
            $this->incluir_moldura = (bool) $dados['incluir_moldura'];
            $this->cor_moldura = $dados['cor_moldura'];
            $this->incluir_rotulo = (bool) $dados['incluir_rotulo'];
            $this->texto_rotulo = $dados['texto_rotulo'];
            $this->nivel_correcao = $dados['nivel_correcao'];

            $this->salvar();
            $this->dispatch('toast', type: 'info', message: 'Configurações padrão restauradas e aplicadas.');
        }
    }

    public function render()
    {
        $todosTipos = TipoDocumentoQrConfig::orderBy('nome')->get();
        if ($todosTipos->isEmpty()) {
            foreach (TipoDocumentoQrConfig::tiposPredefinidos() as $tipo => $config) {
                TipoDocumentoQrConfig::obterParaTipo($tipo);
            }
            $todosTipos = TipoDocumentoQrConfig::orderBy('nome')->get();
        }

        return view('livewire.configuracao-documento-qr', [
            'todosTipos' => $todosTipos,
        ]);
    }
}
