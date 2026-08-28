<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Shared\Traits\HasAuditFields;

class SonaFase extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'sona_fases';

    protected $fillable = [
        'sona_plano_id',
        'nome',
        'descricao',
        'ordem',
        'data_inicio',
        'data_fim_prevista',
        'data_fim_real',
        'status',
        'percentual_conclusao',
        'responsavel_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim_prevista' => 'date',
            'data_fim_real' => 'date',
            'percentual_conclusao' => 'integer',
        ];
    }

    // ===== RELACIONAMENTOS =====

    public function plano()
    {
        return $this->belongsTo(SonaPlano::class, 'sona_plano_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function avancos(): HasMany
    {
        return $this->hasMany(SonaAvanco::class, 'sona_fase_id')->orderBy('data_avanco', 'desc');
    }

    public function imagens(): HasManyThrough
    {
        return $this->hasManyThrough(SonaImagem::class, SonaAvanco::class, 'sona_fase_id', 'sona_avanco_id');
    }

    // ===== SCOPES =====

    public function scopeOrdem($query)
    {
        return $query->orderBy('ordem');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeResponsavel($query, int $userId)
    {
        return $query->where('responsavel_id', $userId);
    }

    // ===== MÉTODOS HELPER =====

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
            'pausada' => 'Pausada',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pendente' => 'secondary',
            'em_andamento' => 'primary',
            'concluida' => 'success',
            'pausada' => 'warning',
            default => 'light',
        };
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
            'status' => 'concluida',
            'data_fim_real' => now()->toDateString(),
            'percentual_conclusao' => 100,
        ]);
        return $this;
    }

    public function addAvanco(string $descricao, ?User $user = null): SonaAvanco
    {
        return $this->avancos()->create([
            'descricao' => $descricao,
            'data_avanco' => now(),
            'registrado_por' => $user?->id,
        ]);
    }

    public function getProgressPercentage(): int
    {
        return $this->percentual_conclusao ?? 0;
    }

    public function isOverdue(): bool
    {
        return $this->data_fim_prevista && $this->data_fim_prevista < now() && $this->status !== 'concluida';
    }

    public function getFullInfo(): string
    {
        return "{$this->nome} (Fase {$this->ordem})";
    }
}
