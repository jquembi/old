<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OperacaoUrbanistica extends Model
{
    use EnviaParaLixeira;

    protected $table = 'operacoes_urbanisticas';

    protected $fillable = [
        'municipio_id', 'titulo', 'descricao',
        'ficheiro_base_caminho', 'ficheiro_base_nome', 'criado_por', 'visibilidade', 'deleted_by',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function croquis(): HasMany
    {
        return $this->hasMany(Croqui::class, 'operacao_id');
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(OperationFile::class, 'operacao_id');
    }

    public function pareceres(): MorphMany
    {
        return $this->morphMany(Parecer::class, 'recurso');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }
}
