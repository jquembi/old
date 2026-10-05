<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tenant_id',
        'salario_minimo',
        'salario_maximo',
        'departamento_id',
        'activo',
    ];

    protected $casts = [
        'salario_minimo' => 'decimal:2',
        'salario_maximo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function funcionarios(): HasMany
    {
        return $this->hasMany(Funcionario::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
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

    public function getNumeroFuncionariosAttribute(): int
    {
        return $this->funcionarios()->where('status', Funcionario::STATUS_ATIVO)->count();
    }

    public function getSalarioMedioAttribute(): float
    {
        return ($this->salario_minimo + $this->salario_maximo) / 2;
    }
}
