<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shared\Traits\HasMunicipioScope;
use App\Shared\Traits\HasSlug;
use App\Shared\Traits\HasAuditFields;

class InstrumentoUrbanistico extends Model
{
    use HasFactory, SoftDeletes, HasMunicipioScope, HasSlug, HasAuditFields;

    protected $table = 'instrumentos_urbanisticos';

    protected $fillable = [
        'municipio_id',
        'titulo',
        'slug',
        'tipo',
        'descricao',
        'numero_documento',
        'data_criacao',
        'data_aprovacao',
        'data_publicacao',
        'file_path',
        'status',
        'aprovado_por',
        'area_abrangencia_km2',
        'beneficiarios_estimados',
        'investimento_estimado',
        'prioridade',
        'observacoes',
    ];

    protected $slugSource = 'titulo';

    protected function casts(): array
    {
        return [
            'data_criacao' => 'date',
            'data_aprovacao' => 'date',
            'data_publicacao' => 'date',
            'area_abrangencia_km2' => 'decimal:2',
            'beneficiarios_estimados' => 'integer',
            'investimento_estimado' => 'decimal:2',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // ===== SCOPES =====

    public function scopeAprovados($query)
    {
        return $query->where('status', 'aprovado');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePrioritarios($query)
    {
        return $query->where('prioridade', 'alta');
    }

    // ===== MÉTODOS HELPER =====

    public function isApproved(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    public function approve(User $user): self
    {
        $this->update([
            'status' => 'aprovado',
            'data_aprovacao' => now()->toDateString(),
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'pum' => 'Plano de Uso e Ocupação do Solo',
            'pp' => 'Plano Parcial',
            'pru' => 'Plano de Reabilitação Urbana',
            'loteamento' => 'Projeto de Loteamento',
            'regulamento' => 'Regulamento',
            default => ucfirst($this->tipo),
        };
    }

    public function getStatusBadge(): string
    {
        $badgeClass = match ($this->status) {
            'pendente' => 'warning',
            'aprovado' => 'success',
            'publicado' => 'info',
            'arquivado' => 'dark',
            default => 'light',
        };

        $label = ucfirst($this->status);
        return "<span class='badge bg-{$badgeClass}'>{$label}</span>";
    }
}
