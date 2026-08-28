<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaInstrumento extends Model
{
    protected $table = 'categoria_instrumentos';

    protected $fillable = ['nome', 'slug', 'descricao', 'ordem'];

    public function tipoInstrumentos(): HasMany
    {
        return $this->hasMany(TipoInstrumento::class, 'categoria_id');
    }
}
