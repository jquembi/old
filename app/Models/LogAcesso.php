<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAcesso extends Model
{
    protected $table = 'logs_acesso';

    protected $fillable = [
        'user_id', 'accao', 'recurso', 'recurso_id',
        'detalhes', 'ip_address', 'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
