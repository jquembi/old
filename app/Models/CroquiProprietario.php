<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CroquiProprietario extends Model
{
    protected $table = 'croqui_proprietarios';

    protected $fillable = ['croqui_id', 'nome', 'bi', 'contacto'];

    public function croqui(): BelongsTo
    {
        return $this->belongsTo(Croqui::class);
    }
}
