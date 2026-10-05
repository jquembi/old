<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tenant_id',
        'responsavel_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'responsavel_id');
    }

    public function funcionarios(): HasMany
    {
        return $this->hasMany(Funcionario::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isAtivo(): bool
    {
        return $this->activo;
    }

    public function getNumeroFuncionariosAttribute(): int
    {
        return $this->funcionarios()->where('status', Funcionario::STATUS_ATIVO)->count();
    }
}
