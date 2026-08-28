<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Shared\Traits\HasAuditFields;
use App\Shared\Traits\HasSlug;

class Operacao extends Model
{
    use HasFactory, HasAuditFields, HasSlug;

    protected $table = 'operacoes';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'municipio_id',
        'status',
        'data_inicio',
        'data_fim',
        'data_aprovacao',
        'aprovado_por',
        'tipo_operacao',
        'area_m2',
        'beneficiarios_estimados',
        'orcamento_estimado',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_aprovacao' => 'datetime',
        'area_m2' => 'decimal:2',
        'beneficiarios_estimados' => 'integer',
        'orcamento_estimado' => 'decimal:2',
    ];

    protected $slugSource = 'nome';

    // ===== RELACIONAMENTOS =====

    public function arquivos()
    {
        return $this->hasMany(OperacaoArquivo::class);
    }

    /**
     * Município da operação.
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Usuário que criou a operação.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que aprovou a operação.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    /**
     * Usuário que alterou pela última vez.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Usuário que deletou.
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // ===== SCOPES =====

    /**
     * Apenas operações ativas.
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', '!=', 'arquivada');
    }

    /**
     * Operações aprovadas.
     */
    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    /**
     * Operações pendentes de aprovação.
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Operações de um município.
     */
    public function scopeDoMunicipio($query, int $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Operações por tipo.
     */
    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo_operacao', $tipo);
    }

    /**
     * Operações dentro de um período.
     */
    public function scopeNoPerodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_inicio', [$dataInicio, $dataFim])
                    ->orWhereBetween('data_fim', [$dataInicio, $dataFim]);
    }

    // ===== MÉTODOS HELPER =====

    /**
     * Verifica se a operação está em rascunho.
     */
    public function isDraft(): bool
    {
        return $this->status === 'rascunho';
    }

    /**
     * Verifica se está pendente de aprovação.
     */
    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se foi aprovada.
     */
    public function isApproved(): bool
    {
        return $this->status === 'aprovada';
    }

    /**
     * Verifica se foi rejeitada.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejeitada';
    }

    /**
     * Verifica se foi arquivada.
     */
    public function isArchived(): bool
    {
        return $this->status === 'arquivada';
    }

    /**
     * Aprova a operação.
     */
    public function approve(User $user): self
    {
        $this->update([
            'status' => 'aprovada',
            'data_aprovacao' => now(),
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    /**
     * Rejeita a operação.
     */
    public function reject(User $user): self
    {
        $this->update([
            'status' => 'rejeitada',
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    /**
     * Submete para aprovação.
     */
    public function submit(): self
    {
        if ($this->isDraft()) {
            $this->update(['status' => 'pendente']);
        }
        return $this;
    }

    /**
     * Arquiva a operação.
     */
    public function archive(): self
    {
        $this->update(['status' => 'arquivada']);
        return $this;
    }

    /**
     * Pode ser editada?
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['rascunho', 'pendente']);
    }

    /**
     * Pode ser aprovada?
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Formata a data de aprovação.
     */
    public function getApprovedAtFormatted(): ?string
    {
        return $this->data_aprovacao?->format('d/m/Y H:i');
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadge(): string
    {
        $badgeClass = match ($this->status) {
            'rascunho' => 'secondary',
            'pendente' => 'warning',
            'aprovada' => 'success',
            'rejeitada' => 'danger',
            'arquivada' => 'dark',
            default => 'light',
        };

        $label = ucfirst($this->status);
        return "<span class='badge bg-{$badgeClass}'>{$label}</span>";
    }

    /**
     * Informações completas da operação.
     */
    public function getFullInfo(): string
    {
        $info = $this->nome;
        if ($this->tipo_operacao) {
            $info .= " ({$this->tipo_operacao})";
        }
        if ($this->municipio) {
            $info .= " - {$this->municipio->nome}";
        }
        return $info;
    }
}
