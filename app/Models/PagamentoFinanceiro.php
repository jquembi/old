<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagamentoFinanceiro extends Model
{
    use SoftDeletes;

    protected $table = 'pagamentos_financeiros';

    protected $fillable = [
        'numero',
        'lancamento_id',
        'taxa_emolumento_id',
        'tenant_id',
        'municipio_id',
        'user_id',
        'data_pagamento',
        'valor',
        'metodo_pagamento',
        'referencia',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_pagamento' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    // ─── Relações ──────────────────────────────────────────

    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(Lancamento::class);
    }

    public function taxaEmolumento(): BelongsTo
    {
        return $this->belongsTo(TaxaEmolumento::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePendente($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeConfirmado($query)
    {
        return $query->where('status', 'confirmado');
    }

    public function scopeCancelado($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopeReembolsado($query)
    {
        return $query->where('status', 'reembolsado');
    }

    // ─── Métodos de estado ───────────────────────────────

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isConfirmado(): bool
    {
        return $this->status === 'confirmado';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
    }

    public function isReembolsado(): bool
    {
        return $this->status === 'reembolsado';
    }

    public function podeConfirmar(): bool
    {
        return $this->isPendente();
    }

    public function podeCancelar(): bool
    {
        return $this->isPendente();
    }

    public function confirmar(): void
    {
        $this->update(['status' => 'confirmado']);
    }

    public function cancelar(): void
    {
        $this->update(['status' => 'cancelado']);
    }
}
