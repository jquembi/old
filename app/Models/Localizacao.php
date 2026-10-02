<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Localizacao extends Model
{
    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tenant_id',
        'municipio_id',
        'departamento_id',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Tipos de localização
    public const TIPO_EDIFICIO = 'edificio';
    public const TIPO_SALA = 'sala';
    public const TIPO_ARMAZEM = 'armazem';
    public const TIPO_GARAGEM = 'garagem';
    public const TIPO_OUTRO = 'outro';

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

    public function patrimonios(): HasMany
    {
        return $this->hasMany(Patrimonio::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorDepartamento($query, int $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function isAtivo(): bool
    {
        return $this->activo;
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            self::TIPO_EDIFICIO => 'Edifício',
            self::TIPO_SALA => 'Sala',
            self::TIPO_ARMAZEM => 'Armazém',
            self::TIPO_GARAGEM => 'Garagem',
            self::TIPO_OUTRO => 'Outro',
            default => ucfirst($this->tipo),
        };
    }

    public function getNumeroPatrimoniosAttribute(): int
    {
        return $this->patrimonios()->where('status', Patrimonio::STATUS_ATIVO)->count();
    }

    public function getValorPatrimoniosAttribute(): float
    {
        return $this->patrimonios()->where('status', Patrimonio::STATUS_ATIVO)->sum('valor_atual') ?? 0;
    }
}
