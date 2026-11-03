<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoLayout extends Model
{
    public const GLOBAL_KEY = '_global';

    public const TIPOS_BLOCO = ['logo', 'texto', 'qr', 'linha', 'retangulo', 'marca_agua'];

    protected $fillable = [
        'tipo_documento', 'nome', 'formato_papel', 'orientacao',
        'blocos', 'institucional', 'ativo', 'atualizado_por',
    ];

    protected $casts = [
        'blocos' => 'array',
        'institucional' => 'array',
        'ativo' => 'boolean',
    ];

    public static function tipos(): array
    {
        return TipoDocumentoQrConfig::tiposPredefinidos();
    }

    public static function institucionalPadrao(): array
    {
        return [
            'orgao' => 'INOTU',
            'subtitulo' => 'Instituto Nacional de Ordenamento do Território e Urbanismo',
            'morada' => 'Luanda, República de Angola',
            'contactos' => 'geral@inotu.ao',
            'logo' => 'imagens/no-background/INOTU.png',
        ];
    }

    public static function institucional(): array
    {
        $guardado = static::query()->where('tipo_documento', self::GLOBAL_KEY)->value('institucional');

        return array_merge(static::institucionalPadrao(), is_array($guardado) ? array_filter($guardado, fn ($v) => $v !== null) : []);
    }

    public static function guardarInstitucional(array $dados, ?int $userId = null): void
    {
        $base = array_intersect_key($dados, static::institucionalPadrao());
        static::updateOrCreate(
            ['tipo_documento' => self::GLOBAL_KEY],
            ['institucional' => $base, 'atualizado_por' => $userId, 'blocos' => []]
        );
    }

    /**
     * Layout efetivo de um tipo: o guardado ou um derivado da configuração de QR legada.
     *
     * @return array{formato_papel:string, orientacao:string, blocos:array, ativo:bool, personalizado:bool}
     */
    public static function efetivo(string $tipo): array
    {
        $registo = static::query()->where('tipo_documento', $tipo)->first();
        if ($registo && is_array($registo->blocos)) {
            return [
                'formato_papel' => $registo->formato_papel,
                'orientacao' => $registo->orientacao,
                'blocos' => $registo->blocos,
                'ativo' => (bool) $registo->ativo,
                'personalizado' => true,
            ];
        }

        return static::padrao($tipo);
    }

    public static function padrao(string $tipo): array
    {
        $qr = TipoDocumentoQrConfig::obterParaTipo($tipo);

        $largura = max(4.0, (float) ($qr->largura_pct ?: 8.0));
        $razao = ($qr->orientacao === 'paisagem') ? 1 / 1.4142 : 1.4142; // altura/largura da página A4
        $alturaPct = round($largura / $razao, 2); // QR quadrado em mm
        $blocos = [[
            'id' => 'qr-principal',
            'tipo' => 'qr',
            'x' => (float) $qr->posicao_x_pct,
            'y' => (float) $qr->posicao_y_pct,
            'w' => $largura,
            'h' => $alturaPct,
            'pagina' => $qr->pagina ?: 'todas',
            'bloqueado' => false,
            'props' => [
                'cor' => $qr->cor_qr,
                'cor_fundo' => $qr->cor_fundo,
                'moldura' => (bool) $qr->incluir_moldura,
                'cor_moldura' => $qr->cor_moldura,
                'rotulo' => $qr->incluir_rotulo ? (string) $qr->texto_rotulo : '',
                'nivel' => $qr->nivel_correcao ?: 'M',
                'quadrado' => true,
            ],
        ]];

        return [
            'formato_papel' => $qr->formato_papel ?: 'A4',
            'orientacao' => $qr->orientacao ?: 'retrato',
            'blocos' => $blocos,
            'ativo' => true,
            'personalizado' => false,
        ];
    }

    /** Modelos prontos de cabeçalho/rodapé para acelerar a criação. */
    public static function modelos(): array
    {
        $cab = [
            ['id' => 'cab-logo', 'tipo' => 'logo', 'x' => 6, 'y' => 3, 'w' => 11, 'h' => 7, 'pagina' => 'todas', 'bloqueado' => false, 'props' => []],
            ['id' => 'cab-orgao', 'tipo' => 'texto', 'x' => 19, 'y' => 3.5, 'w' => 60, 'h' => 3, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['texto' => '{{orgao}}', 'tamanho' => 13, 'negrito' => true, 'alinhamento' => 'L', 'cor' => '#18181b', 'fonte' => 'Helvetica']],
            ['id' => 'cab-sub', 'tipo' => 'texto', 'x' => 19, 'y' => 6.8, 'w' => 70, 'h' => 3, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['texto' => '{{subtitulo}}', 'tamanho' => 8, 'negrito' => false, 'alinhamento' => 'L', 'cor' => '#52525b', 'fonte' => 'Helvetica']],
            ['id' => 'cab-linha', 'tipo' => 'linha', 'x' => 6, 'y' => 11, 'w' => 88, 'h' => 0.2, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['cor' => '#0f766e', 'espessura' => 0.6]],
        ];
        $rod = [
            ['id' => 'rod-linha', 'tipo' => 'linha', 'x' => 6, 'y' => 95, 'w' => 88, 'h' => 0.2, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['cor' => '#d4d4d8', 'espessura' => 0.3]],
            ['id' => 'rod-info', 'tipo' => 'texto', 'x' => 6, 'y' => 96, 'w' => 70, 'h' => 2.5, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['texto' => '{{morada}}  •  {{contactos}}', 'tamanho' => 7, 'negrito' => false, 'alinhamento' => 'L', 'cor' => '#71717a', 'fonte' => 'Helvetica']],
            ['id' => 'rod-pag', 'tipo' => 'texto', 'x' => 76, 'y' => 96, 'w' => 18, 'h' => 2.5, 'pagina' => 'todas', 'bloqueado' => false,
                'props' => ['texto' => 'Página {{pagina}} de {{total}}', 'tamanho' => 7, 'negrito' => false, 'alinhamento' => 'R', 'cor' => '#71717a', 'fonte' => 'Helvetica']],
        ];

        return ['cabecalho' => $cab, 'rodape' => $rod];
    }
}
