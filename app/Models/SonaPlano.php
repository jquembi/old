<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Shared\Traits\HasSlug;
use App\Shared\Traits\HasAuditFields;

class SonaPlano extends Model
{
    use HasFactory, SoftDeletes, HasSlug, HasAuditFields;

    protected $table = 'sona_planos';

    protected $fillable = [
        'municipio_id',
        'titulo',
        'slug',
        'descricao',
        'objetivo',
        'area_intervencao_m2',
        'populacao_alvo',
        'data_inicio',
        'data_fim_prevista',
        'data_fim_real',
        'orcamento_total',
        'orcamento_gasto',
        'percentual_conclusao',
        'status',
        'aprovado_por',
        'observacoes',
    ];

    protected $slugSource = 'titulo';

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim_prevista' => 'date',
            'data_fim_real' => 'date',
            'area_intervencao_m2' => 'decimal:2',
            'populacao_alvo' => 'integer',
            'orcamento_total' => 'decimal:2',
            'orcamento_gasto' => 'decimal:2',
            'percentual_conclusao' => 'integer',
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

    public function fases(): HasMany
    {
        return $this->hasMany(SonaFase::class)->orderBy('ordem');
    }

    // ===== SCOPES =====

    public function scopeAtivos($query)
    {
        return $query->where('status', '!=', 'arquivado');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeAprovados($query)
    {
        return $query->whereNotNull('aprovado_por');
    }

    // ===== MÉTODOS HELPER =====

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'planejamento' => 'Em Planejamento',
            'aprovado' => 'Aprovado',
            'em_andamento' => 'Em Andamento',
            'pausado' => 'Pausado',
            'concluido' => 'Concluído',
            'arquivado' => 'Arquivado',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'planejamento' => 'info',
            'aprovado' => 'success',
            'em_andamento' => 'primary',
            'pausado' => 'warning',
            'concluido' => 'success',
            'arquivado' => 'dark',
            default => 'secondary',
        };
    }

    public function isApproved(): bool
    {
        return $this->aprovado_por !== null;
    }

    public function approve(User $user): self
    {
        $this->update([
            'status' => 'aprovado',
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    public function start(): self
    {
        $this->update([
            'status' => 'em_andamento',
            'data_inicio' => now()->toDateString(),
        ]);
        return $this;
    }

    public function complete(): self
    {
        $this->update([
            'status' => 'concluido',
            'data_fim_real' => now()->toDateString(),
            'percentual_conclusao' => 100,
        ]);
        return $this;
    }

    public function getProgressPercentage(): int
    {
        return $this->percentual_conclusao ?? 0;
    }

    public function getOrcamentoLabel(): string
    {
        if ($this->orcamento_total) {
            $gasto = $this->orcamento_gasto ?? 0;
            $percentual = ($gasto / $this->orcamento_total) * 100;
            return "Gasto: Kz " . number_format($gasto, 2) . " de Kz " . number_format($this->orcamento_total, 2) . " (" . round($percentual) . "%)";
        }
        return 'N/A';
    }

    public function isOverBudget(): bool
    {
        return $this->orcamento_total && $this->orcamento_gasto > $this->orcamento_total;
    }

    public function getDaysRemaining(): ?int
    {
        if (!$this->data_fim_prevista) {
            return null;
        }
        return now()->diffInDays($this->data_fim_prevista, false);
    }

    public function isOverdue(): bool
    {
        return $this->data_fim_prevista && $this->data_fim_prevista < now();
    }
}
