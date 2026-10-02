<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagamentoErp extends Model
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

    protected $casts = [
        'data_pagamento' => 'date',
        'valor' => 'decimal:2',
    ];

    // Métodos de pagamento
    public const METODO_DINHEIRO = 'dinheiro';
    public const METODO_TRANSFERENCIA = 'transferencia';
    public const METODO_MULTICAIXA = 'multicaixa';
    public const METODO_CHEQUE = 'cheque';
    public const METODO_CARTAO = 'cartao';

    // Status
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_REEMBOLSADO = 'reembolsado';

    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(Lancamento::class, 'lancamento_id');
    }

    public function taxaEmolumento(): BelongsTo
    {
        return $this->belongsTo(TaxaEmolumento::class, 'taxa_emolumento_id');
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

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorMetodo($query, string $metodo)
    {
        return $query->where('metodo_pagamento', $metodo);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data_pagamento', [$inicio, $fim]);
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

    public function isReembolsado(): bool
    {
        return $this->status === self::STATUS_REEMBOLSADO;
    }

    public function confirmar(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMADO]);
    }

    public function cancelar(): void
    {
        $this->update(['status' => self::STATUS_CANCELADO]);
    }

    public function reembolsar(): void
    {
        $this->update(['status' => self::STATUS_REEMBOLSADO]);
    }

    public function getMetodoLabel(): string
    {
        return match ($this->metodo_pagamento) {
            self::METODO_DINHEIRO => 'Dinheiro',
            self::METODO_TRANSFERENCIA => 'Transferência',
            self::METODO_MULTICAIXA => 'Multicaixa',
            self::METODO_CHEQUE => 'Cheque',
            self::METODO_CARTAO => 'Cartão',
            default => ucfirst($this->metodo_pagamento),
        };
    }
}
