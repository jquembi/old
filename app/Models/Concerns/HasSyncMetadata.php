<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSyncMetadata
{
    public static function bootHasSyncMetadata(): void
    {
        static::creating(function ($model): void {
            $model->sync_id ??= (string) Str::uuid();
            $model->sync_status ??= 'pending';
            $model->sync_version ??= 1;
        });

        static::updating(function ($model): void {
            if ($model->isDirty(['sync_id', 'sync_status', 'sync_version', 'synced_at', 'sync_error'])) {
                return;
            }

            $model->sync_status = 'pending';
            $model->sync_version = ($model->getOriginal('sync_version') ?? 0) + 1;
            $model->synced_at = null;
        });
    }
}
