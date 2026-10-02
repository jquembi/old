<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NodeSyncOutbox extends Model
{
    protected $table = 'node_sync_outbox';

    protected $fillable = [
        'payload',
        'status',
        'attempts',
        'next_attempt_at',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'next_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function scopeReady($query)
    {
        return $query->where('status', 'pending')
            ->where(fn ($query) => $query->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()));
    }
}
