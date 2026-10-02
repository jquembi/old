<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaGeral extends Model
{
    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tipo',
        'nivel',
        'conta_pai_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Tipos de conta
    public const TIPO_ATIVO = 'ativo';
    public const TIPO_PASSIVO = 'passivo';
    public const TIPO_RECEITA = 'receita';
    public const TIPO_DESPESA = 'despesa';

    public function contaPai(): BelongsTo
    {
        return $this->belongsTo(ContaGeral::class, 'conta_pai_id');
    }

    public function contasFilhas(): HasMany
    {
        return $this->hasMany(ContaGeral::class, 'conta_pai_id');
    }

    public function lancamentosDebito(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'conta_debito_id');
    }

    public function lancamentosCredito(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'conta_credito_id');
    }

    public function taxasEmolumentos(): HasMany
    {
        return $this->hasMany(TaxaEmolumento::class, 'conta_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorNivel($query, int $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeRaiz($query)
    {
        return $query->whereNull('conta_pai_id');
    }

    public function isAtivo(): bool
    {
        return $this->activo;
    }

    public function isContaPai(): bool
    {
        return $this->contasFilhas()->exists();
    }

    public function getCodigoCompletoAttribute(): string
    {
        if ($this->contaPai) {
            return $this->contaPai->codigo_completo . '.' . $this->codigo;
        }
        return $this->codigo;
    }
}
