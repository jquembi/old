<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero',
        'fornecedor_id',
        'requisicao_compra_id',
        'tenant_id',
        'municipio_id',
        'data_inicio',
        'data_fim',
        'descricao',
        'valor_total',
        'tipo',
        'status',
        'arquivo_path',
        'condicoes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'valor_total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Tipos de contrato
    public const TIPO_COMPRA_BENS = 'compra_bens';
    public const TIPO_PRESTACAO_SERVICOS = 'prestacao_servicos';
    public const TIPO_OBRA = 'obra';
    public const TIPO_CONSULTORIA = 'consultoria';

    // Status
    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PENDENTE_ASSINATURA = 'pendente_assinatura';
    public const STATUS_ATIVO = 'ativo';
    public const STATUS_SUSPENSO = 'suspenso';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function requisicaoCompra(): BelongsTo
    {
        return $this->belongsTo(RequisicaoCompra::class, 'requisicao_compra_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function scopeVencidos($query)
    {
        return $query->where('data_fim', '<', now());
    }

    public function scopeAVencer($query, int $dias = 30)
    {
        return $query->whereBetween('data_fim', [now(), now()->addDays($dias)]);
    }

    public function isRascunho(): bool
    {
        return $this->status === self::STATUS_RASCUNHO;
    }

    public function isPendenteAssinatura(): bool
    {
        return $this->status === self::STATUS_PENDENTE_ASSINATURA;
    }

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function isSuspenso(): bool
    {
        return $this->status === self::STATUS_SUSPENSO;
    }

    public function isConcluido(): bool
    {
        return $this->status === self::STATUS_CONCLUIDO;
    }

    public function isCancelado(): bool
    {
        return $this->status === self::STATUS_CANCELADO;
    }

    public function isVencido(): bool
    {
        return $this->data_fim < now();
    }

    public function isAVencer(int $dias = 30): bool
    {
        return $this->data_fim > now() && $this->data_fim <= now()->addDays($dias);
    }

    public function getDuracaoDiasAttribute(): int
    {
        return $this->data_inicio->diffInDays($this->data_fim);
    }

    public function getDiasRestantesAttribute(): int
    {
        if ($this->isConcluido() || $this->isCancelado()) {
            return 0;
        }
        return now()->diffInDays($this->data_fim, false);
    }

    public function getProgressoAttribute(): float
    {
        if ($this->isConcluido() || $this->isCancelado()) {
            return 100;
        }
        
        $total = $this->duracao_dias;
        $passados = $this->data_inicio->diffInDays(now());
        
        return min(100, max(0, ($passados / $total) * 100));
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            self::TIPO_COMPRA_BENS => 'Compra de Bens',
            self::TIPO_PRESTACAO_SERVICOS => 'Prestação de Serviços',
            self::TIPO_OBRA => 'Obra',
            self::TIPO_CONSULTORIA => 'Consultoria',
            default => ucfirst(str_replace('_', ' ', $this->tipo)),
        };
    }
}
