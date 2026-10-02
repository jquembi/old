<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use UsesCentralConnection;

    protected $fillable = [
        'source_tenant_id',
        'target_tenant_id',
        'sync_type',
        'status',
        'records_count',
        'error_message',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'records_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Tipos de sincronização
    public const TYPE_PUSH_TO_CENTRAL = 'push_to_central';
    public const TYPE_PULL_FROM_CENTRAL = 'pull_from_central';
    public const TYPE_PEER_TO_PEER = 'peer_to_peer';
    public const TYPE_FULL_SYNC = 'full_sync';

    // Status
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PARTIAL = 'partial';

    public function sourceTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'source_tenant_id');
    }

    public function targetTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'target_tenant_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }

    public function markAsPartial(int $recordsCount): void
    {
        $this->update([
            'status' => self::STATUS_PARTIAL,
            'records_count' => $recordsCount,
            'completed_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('started_at', 'desc');
    }
}
