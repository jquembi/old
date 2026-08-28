<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

trait EnviaParaLixeira
{
    use SoftDeletes;

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
