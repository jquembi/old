<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use App\Models\Concerns\HasSyncMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Loteamento extends Model
{
    use EnviaParaLixeira, HasSyncMetadata;

    protected $fillable = [
        'municipio_id', 'nome', 'descricao', 'estado', 'localizacao', 'criado_por', 'deleted_by',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(LoteamentoFicheiro::class);
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
