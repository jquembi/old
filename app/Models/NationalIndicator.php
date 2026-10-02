<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NationalIndicator extends Model
{
    use UsesCentralConnection;

    protected $fillable = [
        'tenant_id',
        'indicator_type',
        'value',
        'period_start',
        'period_end',
        'synced_at',
    ];

    protected $casts = [
        'value' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'synced_at' => 'datetime',
    ];

    // Tipos de indicadores
    public const TYPE_PROCESSOS = 'processos';
    public const TYPE_TERRAS = 'terras';
    public const TYPE_RECEITAS = 'receitas';
    public const TYPE_DESPESAS = 'despesas';
    public const TYPE_FISCALIZACOES = 'fiscalizacoes';
    public const TYPE_ARRECADACAO = 'arrecadacao';
    public const TYPE_PRODUTIVIDADE = 'produtividade';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('indicator_type', $type);
    }

    public function scopeInPeriod($query, $start, $end)
    {
        return $query->whereBetween('period_start', [$start, $end])
                     ->whereBetween('period_end', [$start, $end]);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('period_end', 'desc');
    }
}
