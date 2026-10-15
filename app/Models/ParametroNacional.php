<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametroNacional extends Model
{
    use UsesCentralConnection;

    protected $table = 'parametros_nacionais';

    protected $fillable = [
        'grupo', 'chave', 'rotulo', 'descricao', 'tipo', 'valor', 'valor_padrao', 'editavel', 'atualizado_por',
    ];

    protected $casts = [
        'editavel' => 'boolean',
    ];

    public const GRUPOS = [
        'plataforma' => 'Plataforma',
        'sincronizacao' => 'Sincronização',
        'seguranca' => 'Segurança',
        'financeiro' => 'Financeiro',
        'regras' => 'Regras de governação',
    ];

    /**
     * Catálogo de parâmetros oficiais. É semeado de forma idempotente.
     */
    public const CATALOGO = [
        ['plataforma', 'plataforma.nome', 'Nome da plataforma', 'Designação apresentada nos documentos oficiais.', 'texto', 'G.TERRAS'],
        ['plataforma', 'plataforma.moeda', 'Moeda', 'Moeda usada nos indicadores financeiros.', 'texto', 'AOA'],
        ['plataforma', 'plataforma.registos_por_pagina', 'Registos por página', 'Tamanho padrão das listagens nacionais.', 'numero', '15'],
        ['sincronizacao', 'sync.intervalo_minutos', 'Intervalo de sincronização (min)', 'Frequência esperada de comunicação de cada node.', 'numero', '15'],
        ['sincronizacao', 'sync.limite_offline_minutos', 'Limite para node offline (min)', 'Após este tempo sem comunicação o node é considerado em alerta.', 'numero', '60'],
        ['sincronizacao', 'sync.tentativas_maximas', 'Tentativas máximas', 'Número máximo de tentativas antes de marcar uma sincronização como falhada.', 'numero', '5'],
        ['seguranca', 'seguranca.auditoria_retencao_dias', 'Retenção de auditoria (dias)', 'Período mínimo de conservação dos registos de auditoria.', 'numero', '1825'],
        ['seguranca', 'seguranca.exigir_2fa_nacional', 'Exigir 2FA a perfis nacionais', 'Obriga autenticação em dois fatores aos perfis do núcleo.', 'booleano', '0'],
        ['financeiro', 'financeiro.rup_valor_padrao', 'Valor RUP padrão', 'Valor de referência para emolumentos RUP.', 'numero', '0'],
        ['regras', 'regras.aprovacao_dupla', 'Aprovação dupla de operações críticas', 'Exige duas validações para operações críticas nacionais.', 'booleano', '1'],
        ['regras', 'regras.publicacao_requer_verificacao', 'Publicação requer verificação', 'Documentos só são publicados após verificação técnica.', 'booleano', '1'],
        ['regras', 'regras.isolamento_provincial', 'Isolamento provincial', 'Utilizadores provinciais acedem apenas aos dados da sua província.', 'booleano', '1'],
    ];

    public function utilizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }

    public static function semear(): void
    {
        foreach (self::CATALOGO as [$grupo, $chave, $rotulo, $descricao, $tipo, $padrao]) {
            static::firstOrCreate(
                ['chave' => $chave],
                compact('grupo', 'rotulo', 'descricao', 'tipo') + ['valor' => $padrao, 'valor_padrao' => $padrao],
            );
        }
    }

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        $parametro = static::where('chave', $chave)->first();

        if (! $parametro) {
            return $padrao;
        }

        return $parametro->valorTipado();
    }

    public function valorTipado(): mixed
    {
        return match ($this->tipo) {
            'numero' => is_numeric($this->valor) ? $this->valor + 0 : 0,
            'booleano' => in_array((string) $this->valor, ['1', 'true', 'on'], true),
            default => $this->valor,
        };
    }
}
