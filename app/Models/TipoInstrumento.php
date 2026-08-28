<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoInstrumento extends Model
{
    protected $table = 'tipo_instrumentos';

    protected $fillable = ['categoria_id', 'nome', 'slug', 'descricao', 'ordem'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaInstrumento::class, 'categoria_id');
    }

    public function instrumentos(): HasMany
    {
        return $this->hasMany(Instrumento::class);
    }
}
