<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shared\Traits\HasMunicipioScope;
use App\Shared\Traits\HasSlug;
use App\Shared\Traits\HasAuditFields;

class TerrenoOcioso extends Model
{
    use HasFactory, SoftDeletes, HasMunicipioScope, HasSlug, HasAuditFields;

    protected $table = 'terrenos_ociosos';

    protected $fillable = [
        'municipio_id',
        'proprietario',
        'bi_proprietario',
        'slug',
        'endereco',
        'lote',
        'area_m2',
        'area_construida_m2',
        'data_levantamento',
        'motivo_ociosidade',
        'tempo_ociosidade_anos',
        'geometry',
        'pdf_path',
        'sistema_referencia',
        'potencial_uso',
        'status',
        'prioridade_intervencao',
        'contacto_proprietario',
        'observacoes',
    ];

    protected $slugSource = 'proprietario';

    protected function casts(): array
    {
        return [
            'data_levantamento' => 'date',
            'area_m2' => 'decimal:2',
            'area_construida_m2' => 'decimal:2',
            'tempo_ociosidade_anos' => 'integer',
            'geometry' => 'json',
        ];
    }

    // ===== RELACIONAMENTOS =====

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coordenadas()
    {
        return $this->hasMany(TerrenoOciosoCoordenada::class)->orderBy('ordem');
    }

    // ===== SCOPES =====

    public function scopeMotivo($query, string $motivo)
    {
        return $query->where('motivo_ociosidade', $motivo);
    }

    public function scopePrioritarios($query)
    {
        return $query->where('prioridade_intervencao', 'alta');
    }

    public function scopePotencialUso($query, string $uso)
    {
        return $query->where('potencial_uso', $uso);
    }

    public function scopeOciososPorMaisDeAnos($query, int $anos)
    {
        return $query->where('tempo_ociosidade_anos', '>=', $anos);
    }

    public function scopeSemContacto($query)
    {
        return $query->whereNull('contacto_proprietario');
    }

    // ===== MÉTODOS HELPER =====

    public function getMotivoLabel(): string
    {
        return match ($this->motivo_ociosidade) {
            'heranca_indefinida' => 'Herança Indefinida',
            'conflito_propriedade' => 'Conflito de Propriedade',
            'razoes_legais' => 'Razões Legais',
            'especulacao' => 'Especulação Imobiliária',
            'abandono' => 'Abandono',
            'motivos_financeiros' => 'Motivos Financeiros',
            default => ucfirst(str_replace('_', ' ', $this->motivo_ociosidade)),
        };
    }

    public function getPotencialLabel(): string
    {
        return match ($this->potencial_uso) {
            'residencial' => 'Uso Residencial',
            'comercial' => 'Uso Comercial',
            'misto' => 'Uso Misto',
            'institucional' => 'Uso Institucional',
            'verde' => 'Espaço Verde',
            default => ucfirst(str_replace('_', ' ', $this->potencial_uso)),
        };
    }

    public function getPrioridadeBadgeClass(): string
    {
        return match ($this->prioridade_intervencao) {
            'alta' => 'danger',
            'media' => 'warning',
            'baixa' => 'info',
            default => 'secondary',
        };
    }

    public function getAfastamentoArea(): float
    {
        if ($this->area_construida_m2 && $this->area_m2) {
            return (($this->area_m2 - $this->area_construida_m2) / $this->area_m2) * 100;
        }
        return 0;
    }

    public function isVeryOld(): bool
    {
        return $this->tempo_ociosidade_anos >= 10;
    }

    public function hasContact(): bool
    {
        return !empty($this->contacto_proprietario);
    }
}
