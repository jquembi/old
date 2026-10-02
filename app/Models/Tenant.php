<?php

namespace App\Models;

use App\Models\Concerns\UsesCentralConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use UsesCentralConnection;

    protected $fillable = [
        'code',
        'name',
        'type',
        'database_name',
        'database_host',
        'database_port',
        'is_active',
        'sync_status',
        'last_sync_at',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'config' => 'array',
    ];

    // Tipos de tenant
    public const TYPE_CENTRAL = 'central';
    public const TYPE_PROVINCIAL = 'provincial';

    // Status de sincronização
    public const SYNC_ONLINE = 'online';
    public const SYNC_OFFLINE = 'offline';
    public const SYNC_SYNCING = 'syncing';

    public function indicators(): HasMany
    {
        return $this->hasMany(NationalIndicator::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class, 'source_tenant_id');
    }

    public function isCentral(): bool
    {
        return $this->type === self::TYPE_CENTRAL;
    }

    public function isProvincial(): bool
    {
        return $this->type === self::TYPE_PROVINCIAL;
    }

    public function isOnline(): bool
    {
        return $this->sync_status === self::SYNC_ONLINE;
    }

    public function isOffline(): bool
    {
        return $this->sync_status === self::SYNC_OFFLINE;
    }

    public function isSyncing(): bool
    {
        return $this->sync_status === self::SYNC_SYNCING;
    }

    public function markAsOnline(): void
    {
        $this->update([
            'sync_status' => self::SYNC_ONLINE,
            'last_sync_at' => now(),
        ]);
    }

    public function markAsOffline(): void
    {
        $this->update([
            'sync_status' => self::SYNC_OFFLINE,
        ]);
    }

    public function markAsSyncing(): void
    {
        $this->update([
            'sync_status' => self::SYNC_SYNCING,
        ]);
    }

    public function getDatabaseConnection(): array
    {
        $connectionName = config('tenancy.deployment_mode') === 'node'
            ? config('tenancy.database.node_connection')
            : config('tenancy.database.central_connection');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection)) {
            throw new \RuntimeException("Ligação [{$connectionName}] não configurada.");
        }

        return array_filter(array_replace($connection, [
            'database' => $this->database_name,
            'host' => $this->database_host ?? ($connection['host'] ?? null),
            'port' => $this->database_port ?? ($connection['port'] ?? null),
        ]), static fn ($value) => $value !== null);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCentral($query)
    {
        return $query->where('type', self::TYPE_CENTRAL);
    }

    public function scopeProvincial($query)
    {
        return $query->where('type', self::TYPE_PROVINCIAL);
    }

    public function scopeOnline($query)
    {
        return $query->where('sync_status', self::SYNC_ONLINE);
    }
}
