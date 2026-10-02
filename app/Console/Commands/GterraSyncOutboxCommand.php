<?php

namespace App\Console\Commands;

use App\Services\Sync\NodeSyncOutboxService;
use Illuminate\Console\Command;

class GterraSyncOutboxCommand extends Command
{
    protected $signature = 'gterra:sync-outbox {--limit=100}';

    protected $description = 'Envia alterações pendentes de um node ao núcleo sem bloquear a operação local';

    public function handle(NodeSyncOutboxService $outbox): int
    {
        $result = $outbox->deliverReady((int) $this->option('limit'));
        $this->info("Sincronização: {$result['sent']} enviada(s), {$result['failed']} reagendada(s).");

        return self::SUCCESS;
    }
}
