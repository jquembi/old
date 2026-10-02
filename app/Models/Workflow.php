<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'tipo_processo',
        'description',
        'configuracao',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'configuracao' => 'array',
        'activo' => 'boolean',
    ];

    public function processos(): HasMany
    {
        return $this->hasMany(ProcessoWorkflow::class);
    }

    public function getEstados(): array
    {
        return $this->configuracao['estados'] ?? [];
    }

    public function getEstado(string $estado): ?array
    {
        return $this->getEstados()[$estado] ?? null;
    }

    public function getTransicoes(string $estado): array
    {
        $estadoConfig = $this->getEstado($estado);
        return $estadoConfig['transicoes'] ?? [];
    }

    public function isEstadoFinal(string $estado): bool
    {
        $estadoConfig = $this->getEstado($estado);
        return $estadoConfig['final'] ?? false;
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_processo', $tipo);
    }
}
