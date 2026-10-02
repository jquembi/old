<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funcionario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'numero_funcionario',
        'nome_completo',
        'nome_curto',
        'bi',
        'data_nascimento',
        'genero',
        'telefone',
        'email',
        'endereco',
        'municipio_id',
        'tenant_id',
        'departamento_id',
        'cargo_id',
        'data_admissao',
        'data_demissao',
        'salario_base',
        'tipo_contrato',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_admissao' => 'date',
        'data_demissao' => 'date',
        'salario_base' => 'decimal:2',
    ];

    // Tipos de contrato
    public const CONTRATO_EFETIVO = 'efetivo';
    public const CONTRATO_CONTRATO = 'contrato';
    public const CONTRATO_ESTAGIO = 'estagio';
    public const CONTRATO_TEMPORARIO = 'temporario';

    // Status
    public const STATUS_ATIVO = 'ativo';
    public const STATUS_FERIAS = 'ferias';
    public const STATUS_LICENCA = 'licenca';
    public const STATUS_SUSPENSO = 'suspenso';
    public const STATUS_DEMITIDO = 'demitido';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function departamentosComoResponsavel()
    {
        return $this->hasMany(Departamento::class, 'responsavel_id');
    }

    public function scopeAtivo($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    public function scopePorTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePorDepartamento($query, int $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePorCargo($query, int $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function isFerias(): bool
    {
        return $this->status === self::STATUS_FERIAS;
    }

    public function isLicenca(): bool
    {
        return $this->status === self::STATUS_LICENCA;
    }

    public function isSuspenso(): bool
    {
        return $this->status === self::STATUS_SUSPENSO;
    }

    public function isDemitido(): bool
    {
        return $this->status === self::STATUS_DEMITIDO;
    }

    public function getIdadeAttribute(): int
    {
        return $this->data_nascimento->age;
    }

    public function getTempoServicoAttribute(): int
    {
        return $this->data_admissao->diffInYears(now());
    }

    public function demitir(): void
    {
        $this->update([
            'status' => self::STATUS_DEMITIDO,
            'data_demissao' => now(),
        ]);
    }
}
