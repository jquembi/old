<?php

namespace App\Services\Sync;

use App\Models\NodeSyncOutbox;
use Illuminate\Support\Facades\Http;

class NodeSyncOutboxService
{
    public function enqueue(array $data): NodeSyncOutbox
    {
        return NodeSyncOutbox::create([
            'payload' => $data,
            'status' => 'pending',
        ]);
    }

    public function deliverReady(int $limit = 100): array
    {
        if (config('tenancy.deployment_mode') !== 'node' || ! config('tenancy.sync.enabled')) {
            return ['sent' => 0, 'failed' => 0];
        }

        $sent = 0;
        $failed = 0;

        NodeSyncOutbox::ready()->oldest('id')->limit($limit)->each(function (NodeSyncOutbox $outbox) use (&$sent, &$failed): void {
            try {
                $response = Http::connectTimeout(config('tenancy.sync.connect_timeout'))
                    ->timeout(config('tenancy.sync.timeout'))
                    ->acceptJson()
                    ->withToken((string) config('tenancy.sync.token'))
                    ->withHeaders(['X-Tenant' => config('tenancy.node.code')])
                    ->post(config('tenancy.sync.central_url').'/api/gateway/sync/push', [
                        'data' => $outbox->payload,
                    ]);

                $response->throw();

                $outbox->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'last_error' => null,
                ]);
                $sent++;
            } catch (\Throwable $exception) {
                $attempts = $outbox->attempts + 1;
                $outbox->update([
                    'attempts' => $attempts,
                    'next_attempt_at' => now()->addMinutes(config('tenancy.sync.backoff_minutes') * min($attempts, 12)),
                    'last_error' => $exception->getMessage(),
                ]);
                report($exception);
                $failed++;
            }
        });

        return compact('sent', 'failed');
    }
}
