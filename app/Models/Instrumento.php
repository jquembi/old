<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use App\Models\Concerns\HasSyncMetadata;
use App\Shared\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Instrumento extends Model
{
    use EnviaParaLixeira, HasSlug, HasSyncMetadata;

    protected $slugSource = 'titulo';

    protected $fillable = [
        'municipio_id', 'tipo_instrumento_id', 'titulo', 'slug',
        'descricao', 'estado', 'ano', 'criado_por', 'actualizado_por', 'deleted_by',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Aceita o slug e, por compatibilidade com QR codes e links antigos, o id numérico. */
    public function resolveRouteBinding($value, $field = null)
    {
        $registo = $this->where($field ?? 'slug', $value)->first();

        if (! $registo && $field === null && ctype_digit((string) $value)) {
            $registo = $this->where('id', $value)->first();
        }

        return $registo;
    }

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
