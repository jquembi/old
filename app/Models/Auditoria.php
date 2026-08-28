<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = [
        'user_id', 'accao', 'modelo', 'modelo_id',
        'valores_antigos', 'valores_novos', 'ip_address',
        'request_id', 'method', 'url', 'route_name',
        'user_agent', 'session_id', 'contexto',
    ];

    protected function casts(): array
    {
        return [
            'valores_antigos' => 'array',
            'valores_novos' => 'array',
            'contexto' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
