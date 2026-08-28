<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OutroProjecto extends Model
{
    use EnviaParaLixeira;

    protected $table = 'outros_projectos';

    protected $fillable = [
        'municipio_id', 'titulo', 'descricao', 'tipo',
        'entidade_responsavel', 'fase', 'estado',
        'data_inicio', 'data_previsao_fim', 'orcamento',
        'observacoes', 'criado_por', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_previsao_fim' => 'date',
            'orcamento' => 'decimal:2',
        ];
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function pareceres(): MorphMany
    {
        return $this->morphMany(Parecer::class, 'recurso');
    }
}
