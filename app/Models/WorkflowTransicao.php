<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransicao extends Model
{
    protected $fillable = [
        'processo_workflow_id',
        'de_estado',
        'para_estado',
        'realizado_por',
        'dados',
        'observacoes',
    ];

    protected $casts = [
        'dados' => 'array',
    ];

    public function processoWorkflow(): BelongsTo
    {
        return $this->belongsTo(ProcessoWorkflow::class);
    }

    public function realizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }

    public function scopePorWorkflow($query, int $workflowId)
    {
        return $query->whereHas('processoWorkflow', function ($q) use ($workflowId) {
            $q->where('workflow_id', $workflowId);
        });
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
