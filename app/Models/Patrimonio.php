<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patrimonio extends Model
{
    use SoftDeletes;

    protected $table = 'patrimonios';

    protected $fillable = [
        'codigo',
        'nome',
        'descricao',
        'categoria_patrimonio_id',
        'tenant_id',
        'municipio_id',
        'departamento_id',
        'localizacao_id',
        'data_aquisicao',
        'valor_aquisicao',
        'valor_atual',
        'estado',
        'status',
        'numero_serie',
        'fornecedor',
        'observacoes',
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'valor_aquisicao' => 'decimal:2',
        'valor_atual' => 'decimal:2',
    ];

    // Estados
    public const ESTADO_NOVO = 'novo';

    public const ESTADO_BOM = 'bom';

    public const ESTADO_RAZOAVEL = 'razoavel';

    public const ESTADO_MAU = 'mau';

    public const ESTADO_DANIFICADO = 'danificado';

    public const ESTADO_OBSOLETO = 'obsoleto';

    // Status
    public const STATUS_ATIVO = 'ativo';

    public const STATUS_INATIVO = 'inativo';

    public const STATUS_MANUTENCAO = 'manutencao';

    public const STATUS_BAIXA = 'baixa';

    public const STATUS_ALIENADO = 'alienado';

    public function categoriaPatrimonio(): BelongsTo
    {
        return $this->belongsTo(CategoriaPatrimonio::class);
    }

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

    public function localizacao(): BelongsTo
    {
        return $this->belongsTo(Localizacao::class);
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorCategoria($query, int $categoriaId)
    {
        return $query->where('categoria_patrimonio_id', $categoriaId);
    }

    public function scopePorLocalizacao($query, int $localizacaoId)
    {
        return $query->where('localizacao_id', $localizacaoId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function isInativo(): bool
    {
        return $this->status === self::STATUS_INATIVO;
    }

    public function isManutencao(): bool
    {
        return $this->status === self::STATUS_MANUTENCAO;
    }

    public function isBaixa(): bool
    {
        return $this->status === self::STATUS_BAIXA;
    }

    public function isAlienado(): bool
    {
        return $this->status === self::STATUS_ALIENADO;
    }

    public function getIdadePatrimonioAttribute(): int
    {
        return $this->data_aquisicao->diffInYears(now());
    }

    public function getDepreciacaoAnualAttribute(): float
    {
        // Depreciação linear de 10% ao ano (configurável)
        return $this->valor_aquisicao * 0.10;
    }

    public function getValorDepreciadoAttribute(): float
    {
        $anos = $this->idade_patrimonio;
        $depreciacaoTotal = $this->depreciacao_anual * $anos;

        return max(0, $this->valor_aquisicao - $depreciacaoTotal);
    }

    public function getEstadoLabel(): string
    {
        return match ($this->estado) {
            self::ESTADO_NOVO => 'Novo',
            self::ESTADO_BOM => 'Bom',
            self::ESTADO_RAZOAVEL => 'Razoável',
            self::ESTADO_MAU => 'Mau',
            self::ESTADO_DANIFICADO => 'Danificado',
            self::ESTADO_OBSOLETO => 'Obsoleto',
            default => ucfirst($this->estado),
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ATIVO => 'Ativo',
            self::STATUS_INATIVO => 'Inativo',
            self::STATUS_MANUTENCAO => 'Em Manutenção',
            self::STATUS_BAIXA => 'Baixa',
            self::STATUS_ALIENADO => 'Alienado',
            default => ucfirst($this->status),
        };
    }
}
