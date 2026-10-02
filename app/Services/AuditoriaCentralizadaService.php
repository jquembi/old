<?php

namespace App\Services;

use App\Models\NationalAuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaCentralizadaService
{
    public function logAction(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?array $metadata = null
    ): NationalAuditLog {
        $tenant = app('current.tenant');
        $user = Auth::user();
        $request = request();

        return NationalAuditLog::create([
            'tenant_id' => $tenant?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function logLogin(User $user): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_LOGIN,
            entity_type: 'User',
            entity_id: $user->id,
            description: "Utilizador {$user->name} fez login",
            metadata: [
                'email' => $user->email,
                'provincia' => $user->provincia?->nome,
            ]
        );
    }

    public function logLogout(User $user): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_LOGOUT,
            entity_type: 'User',
            entity_id: $user->id,
            description: "Utilizador {$user->name} fez logout"
        );
    }

    public function logCreate(string $entityType, int $entityId, array $data, ?string $description = null): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_CREATE,
            entity_type: $entityType,
            entity_id: $entityId,
            new_values: $data,
            description: $description ?? "Criado {$entityType} #{$entityId}"
        );
    }

    public function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues, ?string $description = null): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_UPDATE,
            entity_type: $entityType,
            entity_id: $entityId,
            old_values: $oldValues,
            new_values: $newValues,
            description: $description ?? "Atualizado {$entityType} #{$entityId}"
        );
    }

    public function logDelete(string $entityType, int $entityId, array $deletedData, ?string $description = null): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_DELETE,
            entity_type: $entityType,
            entity_id: $entityId,
            old_values: $deletedData,
            description: $description ?? "Eliminado {$entityType} #{$entityId}"
        );
    }

    public function logSync(Tenant $sourceTenant, ?Tenant $targetTenant, int $recordsCount, string $status): NationalAuditLog
    {
        return $this->logAction(
            NationalAuditLog::ACTION_SYNC,
            entity_type: 'Tenant',
            entity_id: $sourceTenant->id,
            description: "Sincronização de {$sourceTenant->name}" . ($targetTenant ? " para {$targetTenant->name}" : ''),
            metadata: [
                'source_tenant' => $sourceTenant->code,
                'target_tenant' => $targetTenant?->code,
                'records_count' => $recordsCount,
                'status' => $status,
            ]
        );
    }

    public function getAuditLogs(?int $tenantId = null, ?int $userId = null, ?string $action = null, int $limit = 100)
    {
        $query = NationalAuditLog::query();

        if ($tenantId) {
            $query->byTenant($tenantId);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        if ($action) {
            $query->byAction($action);
        }

        return $query->recent()->limit($limit)->get();
    }

    public function getAuditLogsByPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end, ?int $tenantId = null)
    {
        $query = NationalAuditLog::query()->inPeriod($start, $end);

        if ($tenantId) {
            $query->byTenant($tenantId);
        }

        return $query->recent()->get();
    }

    public function getEntityHistory(string $entityType, int $entityId, int $limit = 50)
    {
        return NationalAuditLog::query()
            ->byEntity($entityType, $entityId)
            ->recent()
            ->limit($limit)
            ->get();
    }

    public function getActivityStats(\Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        $logs = $this->getAuditLogsByPeriod($start, $end);

        return [
            'total_actions' => $logs->count(),
            'by_action' => $logs->groupBy('action')->map->count(),
            'by_tenant' => $logs->groupBy('tenant_id')->map->count(),
            'by_user' => $logs->groupBy('user_id')->map->count(),
            'by_entity' => $logs->groupBy('entity_type')->map->count(),
        ];
    }
}
