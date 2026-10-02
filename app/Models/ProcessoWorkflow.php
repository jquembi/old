<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProcessoWorkflow extends Model
{
    protected $fillable = [
        'workflow_id',
        'processo_id',
        'processo_type',
        'estado_actual',
        'dados',
        'iniciado_por',
        'iniciado_em',
        'concluido_em',
    ];

    protected $casts = [
        'dados' => 'array',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function processo(): MorphTo
    {
        return $this->morphTo();
    }

    public function iniciadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iniciado_por');
    }

    public function transicoes(): HasMany
    {
        return $this->hasMany(WorkflowTransicao::class);
    }

    public function podeTransitar(string $novoEstado): bool
    {
        $config = $this->workflow->configuracao;
        $transicoesPermitidas = $config['estados'][$this->estado_actual]['transicoes'] ?? [];

        return in_array($novoEstado, $transicoesPermitidas);
    }

    public function transitar(string $novoEstado, array $dados = [], ?User $usuario = null): WorkflowTransicao
    {
        if (!$this->podeTransitar($novoEstado)) {
            throw new \Exception('Transição não permitida');
        }

        $transicao = $this->transicoes()->create([
            'de_estado' => $this->estado_actual,
            'para_estado' => $novoEstado,
            'realizado_por' => $usuario?->id ?? auth()->id(),
            'dados' => $dados,
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        $this->update([
            'estado_actual' => $novoEstado,
            'dados' => array_merge($this->dados ?? [], $dados),
        ]);

        // Verificar se é estado final
        if ($this->workflow->isEstadoFinal($novoEstado)) {
            $this->update(['concluido_em' => now()]);
        }

        // Disparar evento de auditoria
        app(\App\Services\AuditoriaCentralizadaService::class)->logAction(
            'workflow_transition',
            entity_type: $this->processo_type,
            entity_id: $this->processo_id,
            description: "Workflow transição de {$this->estado_actual} para {$novoEstado}",
            metadata: [
                'workflow_id' => $this->workflow_id,
                'transicao_id' => $transicao->id,
            ]
        );

        return $transicao;
    }

    public function isConcluido(): bool
    {
        return $this->concluido_em !== null;
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado_actual', $estado);
    }

    public function scopeConcluidos($query)
    {
        return $query->whereNotNull('concluido_em');
    }

    public function scopePendentes($query)
    {
        return $query->whereNull('concluido_em');
    }
}
