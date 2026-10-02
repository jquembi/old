<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequisicaoCompra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero',
        'data',
        'tenant_id',
        'municipio_id',
        'departamento_id',
        'solicitante_id',
        'aprovador_id',
        'descricao',
        'prioridade',
        'status',
        'valor_estimado',
        'lancamento_id',
        'observacoes',
        'aprovado_em',
    ];

    protected $casts = [
        'data' => 'date',
        'valor_estimado' => 'decimal:2',
        'aprovado_em' => 'datetime',
    ];

    // Prioridades
    public const PRIORIDADE_BAIXA = 'baixa';
    public const PRIORIDADE_NORMAL = 'normal';
    public const PRIORIDADE_ALTA = 'alta';
    public const PRIORIDADE_URGENTE = 'urgente';

    // Status
    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PENDENTE_APROVACAO = 'pendente_aprovacao';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_REJEITADO = 'rejeitado';
    public const STATUS_EM_PROCESSAMENTO = 'em_processamento';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'solicitante_id');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'aprovador_id');
    }

    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(Lancamento::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'requisicao_compra_id');
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorPrioridade($query, string $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data', [$inicio, $fim]);
    }

    public function isRascunho(): bool
    {
        return $this->status === self::STATUS_RASCUNHO;
    }

    public function isPendenteAprovacao(): bool
    {
        return $this->status === self::STATUS_PENDENTE_APROVACAO;
    }

    public function isAprovado(): bool
    {
        return $this->status === self::STATUS_APROVADO;
    }

    public function isRejeitado(): bool
    {
        return $this->status === self::STATUS_REJEITADO;
    }

    public function isEmProcessamento(): bool
    {
        return $this->status === self::STATUS_EM_PROCESSAMENTO;
    }

    public function isConcluido(): bool
    {
        return $this->status === self::STATUS_CONCLUIDO;
    }

    public function submeterParaAprovacao(): void
    {
        $this->update(['status' => self::STATUS_PENDENTE_APROVACAO]);
    }

    public function aprovar(Funcionario $aprovador): void
    {
        $this->update([
            'status' => self::STATUS_APROVADO,
            'aprovador_id' => $aprovador->id,
            'aprovado_em' => now(),
        ]);
    }

    public function rejeitar(Funcionario $aprovador): void
    {
        $this->update([
            'status' => self::STATUS_REJEITADO,
            'aprovador_id' => $aprovador->id,
            'aprovado_em' => now(),
        ]);
    }

    public function iniciarProcessamento(): void
    {
        $this->update(['status' => self::STATUS_EM_PROCESSAMENTO]);
    }

    public function concluir(): void
    {
        $this->update(['status' => self::STATUS_CONCLUIDO]);
    }

    public function cancelar(): void
    {
        $this->update(['status' => self::STATUS_CANCELADO]);
    }

    public function getPrioridadeBadgeClass(): string
    {
        return match ($this->prioridade) {
            self::PRIORIDADE_URGENTE => 'danger',
            self::PRIORIDADE_ALTA => 'warning',
            self::PRIORIDADE_NORMAL => 'info',
            self::PRIORIDADE_BAIXA => 'secondary',
            default => 'secondary',
        };
    }
}
