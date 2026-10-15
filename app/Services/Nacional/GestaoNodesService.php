<?php

namespace App\Services\Nacional;

use App\Models\SyncLog;
use App\Models\Tenant;
use App\Services\AuditoriaCentralizadaService;

class GestaoNodesService
{
    /**
     * @return array{mensagem:string,tipo:string}
     */
    public function alternarAtivo(Tenant $tenant): array
    {
        if ($tenant->isCentral()) {
            return ['mensagem' => 'O núcleo central não pode ser desativado.', 'tipo' => 'error'];
        }

        $antes = ['is_active' => $tenant->is_active];
        $tenant->update(['is_active' => ! $tenant->is_active]);

        app(AuditoriaCentralizadaService::class)->logUpdate(
            'tenant',
            $tenant->id,
            $antes,
            ['is_active' => $tenant->is_active],
            ($tenant->is_active ? 'Ativação' : 'Desativação')." do node {$tenant->code}",
        );

        return ['mensagem' => "Node {$tenant->code} ".($tenant->is_active ? 'ativado.' : 'desativado.'), 'tipo' => 'success'];
    }

    /**
     * Regista o pedido de sincronização completa; o node processa-o na próxima comunicação.
     *
     * @return array{mensagem:string,tipo:string}
     */
    public function pedirSincronizacao(Tenant $tenant): array
    {
        if ($tenant->isCentral()) {
            return ['mensagem' => 'O núcleo central não sincroniza consigo próprio.', 'tipo' => 'error'];
        }

        if (! $tenant->is_active) {
            return ['mensagem' => 'Ative o node antes de pedir sincronização.', 'tipo' => 'error'];
        }

        $pendente = SyncLog::pending()
            ->where('source_tenant_id', $tenant->id)
            ->where('sync_type', SyncLog::TYPE_FULL_SYNC)
            ->exists();

        if ($pendente) {
            return ['mensagem' => 'Já existe um pedido de sincronização pendente para este node.', 'tipo' => 'warning'];
        }

        $central = Tenant::central()->first();

        $log = SyncLog::create([
            'source_tenant_id' => $tenant->id,
            'target_tenant_id' => $central?->id,
            'sync_type' => SyncLog::TYPE_FULL_SYNC,
            'status' => SyncLog::STATUS_PENDING,
            'started_at' => now(),
            'metadata' => ['pedido_por' => auth()->id(), 'origem' => 'supervisao-nodes'],
        ]);

        app(AuditoriaCentralizadaService::class)->logAction(
            'sync', 'sync_log', $log->id, null, null, "Pedido de sincronização completa ao node {$tenant->code}",
        );

        return ['mensagem' => "Pedido de sincronização registado para {$tenant->code}.", 'tipo' => 'success'];
    }
}
