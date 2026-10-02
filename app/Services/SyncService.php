<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncService
{
    public function syncToCentral(Tenant $tenant, array $data): SyncLog
    {
        $centralTenant = Tenant::where('type', 'central')->first();
        
        if (!$centralTenant) {
            throw new \Exception('Tenant central não encontrado');
        }

        $syncLog = SyncLog::create([
            'source_tenant_id' => $tenant->id,
            'target_tenant_id' => $centralTenant->id,
            'sync_type' => SyncLog::TYPE_PUSH_TO_CENTRAL,
            'status' => SyncLog::STATUS_PENDING,
            'started_at' => now(),
        ]);

        try {
            DB::connection($this->centralConnection())->transaction(function () use ($data, $syncLog) {
                foreach ($data as $entity => $records) {
                    $this->syncEntity($entity, $records, $syncLog);
                }
            });

            $syncLog->update([
                'status' => SyncLog::STATUS_SUCCESS,
                'completed_at' => now(),
            ]);

            // Log de auditoria
            app(AuditoriaCentralizadaService::class)->logSync(
                $tenant,
                $centralTenant,
                $syncLog->records_count,
                'success'
            );

            return $syncLog;
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => SyncLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Log de auditoria
            app(AuditoriaCentralizadaService::class)->logSync(
                $tenant,
                $centralTenant,
                $syncLog->records_count,
                'failed'
            );

            throw $e;
        }
    }

    private function syncEntity(string $entity, array $records, SyncLog $syncLog): void
    {
        $this->assertSyncableEntity($entity);

        foreach ($records as $record) {
            $centralRecord = DB::connection($this->centralConnection())
                ->table($entity)
                ->where('sync_id', $record['sync_id'])
                ->first();

            if ($centralRecord) {
                // Verificar conflitos
                if ($centralRecord->sync_version >= $record['sync_version']) {
                    $this->handleConflict($entity, $record, $centralRecord);
                    continue;
                }

                // Update
                DB::connection($this->centralConnection())
                    ->table($entity)
                    ->where('sync_id', $record['sync_id'])
                    ->update($record);
            } else {
                // Insert
                DB::connection($this->centralConnection())->table($entity)->insert($record);
            }
        }

        $syncLog->increment('records_count', count($records));
    }

    private function handleConflict(string $entity, array $incoming, object $existing): void
    {
        // Marcar como conflito
        DB::connection($this->centralConnection())
            ->table($entity)
            ->where('sync_id', $incoming['sync_id'])
            ->update([
                'sync_status' => 'conflict',
                'sync_error' => 'Conflito de versão: versão local ' . $existing->sync_version . ' vs versão recebida ' . $incoming['sync_version'],
            ]);

        // Log do conflito
        app(AuditoriaCentralizadaService::class)->logAction(
            'sync_conflict',
            entity_type: $entity,
            entity_id: $existing->id,
            description: "Conflito de sincronização em {$entity}",
            metadata: [
                'sync_id' => $incoming['sync_id'],
                'local_version' => $existing->sync_version,
                'incoming_version' => $incoming['sync_version'],
            ]
        );
    }

    public function syncFromCentral(Tenant $tenant, array $data): SyncLog
    {
        $centralTenant = Tenant::where('type', 'central')->first();
        
        if (!$centralTenant) {
            throw new \Exception('Tenant central não encontrado');
        }

        $syncLog = SyncLog::create([
            'source_tenant_id' => $centralTenant->id,
            'target_tenant_id' => $tenant->id,
            'sync_type' => SyncLog::TYPE_PULL_FROM_CENTRAL,
            'status' => SyncLog::STATUS_PENDING,
            'started_at' => now(),
        ]);

        try {
            DB::connection($tenant->database_name)->transaction(function () use ($data, $syncLog) {
                foreach ($data as $entity => $records) {
                    $this->syncEntityToLocal($entity, $records, $syncLog);
                }
            });

            $syncLog->update([
                'status' => SyncLog::STATUS_SUCCESS,
                'completed_at' => now(),
            ]);

            return $syncLog;
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => SyncLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    private function syncEntityToLocal(string $entity, array $records, SyncLog $syncLog): void
    {
        $this->assertSyncableEntity($entity);

        foreach ($records as $record) {
            $localRecord = DB::connection('tenant')
                ->table($entity)
                ->where('sync_id', $record['sync_id'])
                ->first();

            if ($localRecord) {
                // Verificar conflitos
                if ($localRecord->sync_version >= $record['sync_version']) {
                    $this->handleLocalConflict($entity, $record, $localRecord);
                    continue;
                }

                // Update
                DB::connection('tenant')
                    ->table($entity)
                    ->where('sync_id', $record['sync_id'])
                    ->update($record);
            } else {
                // Insert
                DB::connection('tenant')->table($entity)->insert($record);
            }
        }

        $syncLog->increment('records_count', count($records));
    }

    private function handleLocalConflict(string $entity, array $incoming, object $existing): void
    {
        DB::connection('tenant')
            ->table($entity)
            ->where('sync_id', $incoming['sync_id'])
            ->update([
                'sync_status' => 'conflict',
                'sync_error' => 'Conflito de versão: versão local ' . $existing->sync_version . ' vs versão recebida ' . $incoming['sync_version'],
            ]);
    }

    private function assertSyncableEntity(string $entity): void
    {
        if (! in_array($entity, config('tenancy.sync.entities', []), true)) {
            throw new \InvalidArgumentException("A entidade [{$entity}] não pode ser sincronizada.");
        }
    }

    private function centralConnection(): string
    {
        return config('tenancy.database.central_connection');
    }

    public function getPendingSyncs(Tenant $tenant): \Illuminate\Support\Collection
    {
        return SyncLog::where('source_tenant_id', $tenant->id)
            ->where('status', SyncLog::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();
    }

    public function getSyncHistory(Tenant $tenant, int $limit = 50): \Illuminate\Support\Collection
    {
        return SyncLog::where('source_tenant_id', $tenant->id)
            ->orWhere('target_tenant_id', $tenant->id)
            ->recent()
            ->limit($limit)
            ->get();
    }

    public function markTenantOnline(Tenant $tenant): void
    {
        $tenant->markAsOnline();
        
        app(AuditoriaCentralizadaService::class)->logAction(
            'node_online',
            entity_type: 'Tenant',
            entity_id: $tenant->id,
            description: "Tenant {$tenant->name} ficou online"
        );
    }

    public function markTenantOffline(Tenant $tenant): void
    {
        $tenant->markAsOffline();
        
        app(AuditoriaCentralizadaService::class)->logAction(
            'node_offline',
            entity_type: 'Tenant',
            entity_id: $tenant->id,
            description: "Tenant {$tenant->name} ficou offline"
        );
    }
}
