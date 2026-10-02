<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use App\Models\Concerns\HasSyncMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Instrumento extends Model
{
    use EnviaParaLixeira, HasSyncMetadata;

    protected $fillable = [
        'municipio_id', 'tipo_instrumento_id', 'titulo',
        'descricao', 'estado', 'ano', 'criado_por', 'actualizado_por', 'deleted_by',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tipoInstrumento(): BelongsTo
    {
        return $this->belongsTo(TipoInstrumento::class);
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(InstrumentoFicheiro::class);
    }

    public function pareceres(): MorphMany
    {
        return $this->morphMany(Parecer::class, 'recurso');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}
