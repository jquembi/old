<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaPatrimonio extends Model
{
    protected $table = 'categorias_patrimonio';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tenant_id',
        'categoria_pai_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoriaPai(): BelongsTo
    {
        return $this->belongsTo(CategoriaPatrimonio::class, 'categoria_pai_id');
    }

    public function categoriasFilhas(): HasMany
    {
        return $this->hasMany(CategoriaPatrimonio::class, 'categoria_pai_id');
    }

    public function patrimonios(): HasMany
    {
        return $this->hasMany(Patrimonio::class, 'categoria_patrimonio_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeRaiz($query)
    {
        return $query->whereNull('categoria_pai_id');
    }

    public function isAtivo(): bool
    {
        return $this->activo;
    }

    public function isCategoriaPai(): bool
    {
        return $this->categoriasFilhas()->exists();
    }

    public function getCodigoCompletoAttribute(): string
    {
        if ($this->categoriaPai) {
            return $this->categoriaPai->codigo_completo.'.'.$this->codigo;
        }

        return $this->codigo;
    }

    public function getNumeroPatrimoniosAttribute(): int
    {
        return $this->patrimonios()->where('status', Patrimonio::STATUS_ATIVO)->count();
    }

    public function getValorTotalPatrimoniosAttribute(): float
    {
        return $this->patrimonios()->where('status', Patrimonio::STATUS_ATIVO)->sum('valor_atual') ?? 0;
    }
}
