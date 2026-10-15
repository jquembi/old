<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoQrConfig extends Model
{
    protected $fillable = [
        'tipo_documento',
        'nome',
        'descricao',
        'formato_papel',
        'orientacao',
        'pagina',
        'posicao_x_pct',
        'posicao_y_pct',
        'largura_pct',
        'tamanho_mm',
        'cor_qr',
        'cor_fundo',
        'incluir_moldura',
        'cor_moldura',
        'incluir_rotulo',
        'texto_rotulo',
        'nivel_correcao',
        'ativo',
        'atualizado_por',
    ];

    protected $casts = [
        'posicao_x_pct' => 'float',
        'posicao_y_pct' => 'float',
        'largura_pct' => 'float',
        'tamanho_mm' => 'float',
        'incluir_moldura' => 'boolean',
        'incluir_rotulo' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }

    /**
     * Retorna a lista de tipos de documento suportados com suas configurações predefinidas.
     */
    public static function tiposPredefinidos(): array
    {
        return [
            'croqui' => [
                'nome' => 'Croqui de Localização',
                'descricao' => 'Plantas topográficas e croquis georreferenciados de parcelas e terrenos.',
                'formato_papel' => 'A4',
                'orientacao' => 'paisagem',
                'pagina' => 'todas',
                'posicao_x_pct' => 88.00,
                'posicao_y_pct' => 82.00,
                'largura_pct' => 7.50,
                'tamanho_mm' => 20.00,
                'cor_qr' => '#18181b',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#e4e4e7',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'AUTENTICIDADE • CROQUI',
                'nivel_correcao' => 'M',
            ],
            'memoria_descritiva' => [
                'nome' => 'Memória Descritiva',
                'descricao' => 'Relatórios técnicos com confrontações, vértices UTM e especificações de terreno.',
                'formato_papel' => 'A4',
                'orientacao' => 'retrato',
                'pagina' => 'primeira',
                'posicao_x_pct' => 86.00,
                'posicao_y_pct' => 4.00,
                'largura_pct' => 8.50,
                'tamanho_mm' => 22.00,
                'cor_qr' => '#09090b',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#e4e4e7',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'CERTIFICAÇÃO OFICIAL',
                'nivel_correcao' => 'M',
            ],
            'instrumento' => [
                'nome' => 'Instrumento de Planeamento Territorial (PMOT)',
                'descricao' => 'Planos Directores Municipais (PDM), Planos de Pormenor e Planos de Urbanização.',
                'formato_papel' => 'A4',
                'orientacao' => 'retrato',
                'pagina' => 'primeira',
                'posicao_x_pct' => 86.00,
                'posicao_y_pct' => 4.00,
                'largura_pct' => 8.50,
                'tamanho_mm' => 22.00,
                'cor_qr' => '#b91c1c',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#fecaca',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'VALIDAÇÃO PMOT • INOTU',
                'nivel_correcao' => 'M',
            ],
            'loteamento' => [
                'nome' => 'Operação de Loteamento',
                'descricao' => 'Plantas de síntese e cadernos de encargos de loteamentos urbanos aprovados.',
                'formato_papel' => 'A3',
                'orientacao' => 'paisagem',
                'pagina' => 'primeira',
                'posicao_x_pct' => 89.00,
                'posicao_y_pct' => 84.00,
                'largura_pct' => 6.50,
                'tamanho_mm' => 24.00,
                'cor_qr' => '#b45309',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#fef3c7',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'REGISTO LOTEAMENTO',
                'nivel_correcao' => 'M',
            ],
            'parecer' => [
                'nome' => 'Parecer Técnico & Homologação',
                'descricao' => 'Documentos de deliberação técnica, conformidade urbanística e homologações.',
                'formato_papel' => 'A4',
                'orientacao' => 'retrato',
                'pagina' => 'ultima',
                'posicao_x_pct' => 84.00,
                'posicao_y_pct' => 84.00,
                'largura_pct' => 8.50,
                'tamanho_mm' => 22.00,
                'cor_qr' => '#1d4ed8',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#dbeafe',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'PARECER TÉCNICO VÁLIDO',
                'nivel_correcao' => 'M',
            ],
            'sona' => [
                'nome' => 'Projecto SONA & Obras',
                'descricao' => 'Projectos de infraestruturas, requalificação e relatórios de avanço de obras públicas.',
                'formato_papel' => 'A4',
                'orientacao' => 'retrato',
                'pagina' => 'primeira',
                'posicao_x_pct' => 86.00,
                'posicao_y_pct' => 4.00,
                'largura_pct' => 8.00,
                'tamanho_mm' => 20.00,
                'cor_qr' => '#0f766e',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#ccfbf1',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'PROGRAMA SONA • OFICIAL',
                'nivel_correcao' => 'M',
            ],
            'outro_projeto' => [
                'nome' => 'Outros Projectos de Requalificação',
                'descricao' => 'Iniciativas urbanísticas municipais diversas e projectos de intervenção comunitária.',
                'formato_papel' => 'A4',
                'orientacao' => 'retrato',
                'pagina' => 'primeira',
                'posicao_x_pct' => 86.00,
                'posicao_y_pct' => 4.00,
                'largura_pct' => 8.00,
                'tamanho_mm' => 20.00,
                'cor_qr' => '#0e7490',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#cffafe',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'PROJECTO APROVADO',
                'nivel_correcao' => 'M',
            ],
            'carta_municipal' => [
                'nome' => 'Carta Municipal & Cartografia',
                'descricao' => 'Cartografia municipal oficial, limites territoriais e mapas de ocupação de solo.',
                'formato_papel' => 'A3',
                'orientacao' => 'paisagem',
                'pagina' => 'todas',
                'posicao_x_pct' => 89.00,
                'posicao_y_pct' => 84.00,
                'largura_pct' => 6.50,
                'tamanho_mm' => 24.00,
                'cor_qr' => '#047857',
                'cor_fundo' => '#ffffff',
                'incluir_moldura' => true,
                'cor_moldura' => '#d1fae5',
                'incluir_rotulo' => true,
                'texto_rotulo' => 'CARTA OFICIAL DE ANGOLA',
                'nivel_correcao' => 'H',
            ],
        ];
    }

    /**
     * Retorna a configuração de um tipo ou cria a predefinida caso ainda não exista.
     */
    public static function obterParaTipo(string $tipo): self
    {
        $config = static::where('tipo_documento', $tipo)->first();

        if ($config) {
            return $config;
        }

        $predefinidos = static::tiposPredefinidos();
        $dados = $predefinidos[$tipo] ?? [
            'nome' => ucfirst(str_replace('_', ' ', $tipo)),
            'descricao' => 'Configuração personalizada de QR Code para ' . $tipo,
            'formato_papel' => 'A4',
            'orientacao' => 'retrato',
            'pagina' => 'todas',
            'posicao_x_pct' => 80.00,
            'posicao_y_pct' => 5.00,
            'largura_pct' => 15.00,
            'tamanho_mm' => 30.00,
            'cor_qr' => '#18181b',
            'cor_fundo' => '#ffffff',
            'incluir_moldura' => true,
            'cor_moldura' => '#e4e4e7',
            'incluir_rotulo' => true,
            'texto_rotulo' => 'AUTENTICIDADE • QR CODE',
            'nivel_correcao' => 'M',
        ];

        return static::create(array_merge($dados, [
            'tipo_documento' => $tipo,
            'ativo' => true,
        ]));
    }
}
