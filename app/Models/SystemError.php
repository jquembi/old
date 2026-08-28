<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemError extends Model
{
    protected $fillable = [
        'fingerprint',
        'status',
        'exception_class',
        'message',
        'file',
        'line',
        'environment',
        'url',
        'method',
        'ip',
        'user_agent',
        'user_id',
        'trace',
        'context',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'resolved_at',
        'resolved_by',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function estaResolvido(): bool
    {
        return $this->status === 'resolvido';
    }
}
