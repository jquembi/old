<?php

namespace App\Http\Controllers\ApiGateway;

use App\Http\Controllers\Controller;
use App\Models\OfflineOperation;
use App\Models\SyncLog;
use App\Services\Sync\NodeSyncOutboxService;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function __construct(
        private SyncService $syncService,
        private NodeSyncOutboxService $outboxService,
    ) {}

    public function pushToCentral(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant não encontrado',
            ], 404);
        }

        $request->validate([
            'data' => 'required|array',
            'data.*' => 'array',
        ]);

        // Num node, a sincronização é sempre assíncrona. A indisponibilidade
        // do núcleo não bloqueia nem altera operações locais.
        if (config('tenancy.deployment_mode') === 'node') {
            $outbox = $this->outboxService->enqueue($request->data);

            return response()->json([
                'success' => true,
                'data' => [
                    'outbox_id' => $outbox->id,
                    'status' => $outbox->status,
                ],
                'message' => 'Sincronização guardada localmente para envio assíncrono.',
            ], 202);
        }

        try {
            $syncLog = $this->syncService->syncToCentral($tenant, $request->data);

            return response()->json([
                'success' => true,
                'data' => [
                    'sync_log_id' => $syncLog->id,
                    'status' => $syncLog->status,
                    'records_count' => $syncLog->records_count,
                ],
                'message' => 'Sincronização iniciada com sucesso',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pullFromCentral(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant não encontrado',
            ], 404);
        }

        $request->validate([
            'data' => 'required|array',
            'data.*' => 'array',
        ]);

        try {
            $syncLog = $this->syncService->syncFromCentral($tenant, $request->data);

            return response()->json([
                'success' => true,
                'data' => [
                    'sync_log_id' => $syncLog->id,
                    'status' => $syncLog->status,
                    'records_count' => $syncLog->records_count,
                ],
                'message' => 'Sincronização iniciada com sucesso',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function status(SyncLog $syncLog): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if ($syncLog->source_tenant_id !== $tenant->id && $syncLog->target_tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado a este log de sincronização',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $syncLog->id,
                'status' => $syncLog->status,
                'sync_type' => $syncLog->sync_type,
                'records_count' => $syncLog->records_count,
                'started_at' => $syncLog->started_at,
                'completed_at' => $syncLog->completed_at,
                'error_message' => $syncLog->error_message,
            ],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        $limit = $request->input('limit', 50);
        $history = $this->syncService->getSyncHistory($tenant, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    public function queueOfflineOperation(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        $request->validate([
            'operation_type' => 'required|in:create,update,delete',
            'entity_type' => 'required|string',
            'payload' => 'required|array',
            'priority' => 'in:low,normal,high,urgent',
        ]);

        $operation = OfflineOperation::create([
            'tenant_id' => $tenant->id,
            'operation_type' => $request->operation_type,
            'entity_type' => $request->entity_type,
            'payload' => $request->payload,
            'priority' => $request->input('priority', 'normal'),
            'status' => OfflineOperation::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $operation->id,
                'status' => $operation->status,
            ],
            'message' => 'Operação offline enfileirada',
        ], 201);
    }

    public function pendingOfflineOperations(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        $operations = OfflineOperation::byTenant($tenant->id)
            ->pending()
            ->scheduled()
            ->orderedByPriority()
            ->limit($request->input('limit', 100))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $operations,
        ]);
    }

    public function processOfflineOperations(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        $operations = OfflineOperation::byTenant($tenant->id)
            ->pending()
            ->scheduled()
            ->orderedByPriority()
            ->limit($request->input('limit', 10))
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($operations as $operation) {
            try {
                $operation->markAsProcessing();
                
                // Processar operação baseada no tipo
                $this->processOperation($operation);
                
                $operation->markAsCompleted();
                $processed++;
            } catch (\Exception $e) {
                $operation->markAsFailed($e->getMessage());
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'processed' => $processed,
                'failed' => $failed,
            ],
            'message' => "Processadas {$processed} operações, {$failed} falharam",
        ]);
    }

    private function processOperation(OfflineOperation $operation): void
    {
        // Implementação baseada no tipo de operação
        match ($operation->operation_type) {
            OfflineOperation::TYPE_CREATE => $this->processCreate($operation),
            OfflineOperation::TYPE_UPDATE => $this->processUpdate($operation),
            OfflineOperation::TYPE_DELETE => $this->processDelete($operation),
        };
    }

    private function processCreate(OfflineOperation $operation): void
    {
        // Lógica para processar criação
        $payload = $operation->payload;
        $entity = $operation->entity_type;
        
        // Implementar lógica específica por entidade
        // Ex: DB::table($entity)->insert($payload);
    }

    private function processUpdate(OfflineOperation $operation): void
    {
        // Lógica para processar atualização
        $payload = $operation->payload;
        $entity = $operation->entity_type;
        
        // Implementar lógica específica por entidade
        // Ex: DB::table($entity)->where('id', $payload['id'])->update($payload);
    }

    private function processDelete(OfflineOperation $operation): void
    {
        // Lógica para processar eliminação
        $payload = $operation->payload;
        $entity = $operation->entity_type;
        
        // Implementar lógica específica por entidade
        // Ex: DB::table($entity)->where('id', $payload['id'])->delete();
    }
}
