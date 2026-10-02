<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lancamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero',
        'data',
        'conta_debito_id',
        'conta_credito_id',
        'valor',
        'descricao',
        'tipo',
        'status',
        'tenant_id',
        'municipio_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Tipos de lançamento
    public const TIPO_RECEITA = 'receita';
    public const TIPO_DESPESA = 'despesa';
    public const TIPO_TRANSFERENCIA = 'transferencia';

    // Status
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_CANCELADO = 'cancelado';

    public function contaDebito(): BelongsTo
    {
        return $this->belongsTo(ContaGeral::class, 'conta_debito_id');
    }

    public function contaCredito(): BelongsTo
    {
        return $this->belongsTo(ContaGeral::class, 'conta_credito_id');
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

    public function pagamentos()
    {
        return $this->hasMany(PagamentoErp::class, 'lancamento_id');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorMunicipio($query, int $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data', [$inicio, $fim]);
    }

    public function isPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function isConfirmado(): bool
    {
        return $this->status === self::STATUS_CONFIRMADO;
    }

    public function isCancelado(): bool
    {
        return $this->status === self::STATUS_CANCELADO;
    }

    public function aprovar(User $user): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMADO,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function cancelar(): void
    {
        $this->update(['status' => self::STATUS_CANCELADO]);
    }

    public function getSaldoPagamentosAttribute(): float
    {
        return $this->pagamentos()->where('status', 'confirmado')->sum('valor');
    }

    public function getSaldoPendenteAttribute(): float
    {
        return $this->valor - $this->saldo_pagamentos;
    }
}
