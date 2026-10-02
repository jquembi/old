<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxaEmolumento extends Model
{
    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'tipo',
        'valor',
        'tipo_valor',
        'percentual',
        'unidade',
        'conta_id',
        'activo',
        'valido_de',
        'valido_ate',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'percentual' => 'decimal:2',
        'activo' => 'boolean',
        'valido_de' => 'date',
        'valido_ate' => 'date',
    ];

    // Tipos
    public const TIPO_TAXA = 'taxa';
    public const TIPO_EMOLUMENTO = 'emolumento';
    public const TIPO_MULTA = 'multa';

    // Tipos de valor
    public const TIPO_VALOR_FIXO = 'fixo';
    public const TIPO_VALOR_PERCENTUAL = 'percentual';
    public const TIPO_VALOR_VARIAVEL = 'variavel';

    public function conta(): BelongsTo
    {
        return $this->belongsTo(ContaGeral::class, 'conta_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(PagamentoErp::class, 'taxa_emolumento_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeValidoEm($query, $data)
    {
        return $query->where(function ($q) use ($data) {
            $q->whereNull('valido_de')
              ->orWhere('valido_de', '<=', $data);
        })->where(function ($q) use ($data) {
            $q->whereNull('valido_ate')
              ->orWhere('valido_ate', '>=', $data);
        });
    }

    public function isAtivo(): bool
    {
        return $this->activo && $this->isValidoEm(now());
    }

    public function isValidoEm($data): bool
    {
        $inicio = $this->valido_de ?? '1900-01-01';
        $fim = $this->valido_ate ?? '2099-12-31';
        
        return $data >= $inicio && $data <= $fim;
    }

    public function calcularValor(float $base = null): float
    {
        return match ($this->tipo_valor) {
            self::TIPO_VALOR_FIXO => $this->valor,
            self::TIPO_VALOR_PERCENTUAL => ($base ?? 0) * ($this->percentual / 100),
            self::TIPO_VALOR_VARIAVEL => $base ?? $this->valor,
            default => $this->valor,
        };
    }
}
