<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'nome',
        'nif',
        'telefone',
        'email',
        'endereco',
        'municipio_id',
        'tenant_id',
        'tipo',
        'activo',
        'observacoes',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Tipos de fornecedor
    public const TIPO_BENS = 'bens';
    public const TIPO_SERVICOS = 'servicos';
    public const TIPO_AMBOS = 'ambos';

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
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

    public function isAtivo(): bool
    {
        return $this->activo;
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            self::TIPO_BENS => 'Bens',
            self::TIPO_SERVICOS => 'Serviços',
            self::TIPO_AMBOS => 'Bens e Serviços',
            default => ucfirst($this->tipo),
        };
    }
}
