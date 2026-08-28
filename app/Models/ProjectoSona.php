<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectoSona extends Model
{
    use EnviaParaLixeira;

    protected $table = 'projecto_sona';

    protected $fillable = [
        'municipio_id', 'titulo', 'descricao', 'tipo',
        'fase', 'estado', 'criado_por', 'deleted_by',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(ProjectoSonaFicheiro::class);
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
