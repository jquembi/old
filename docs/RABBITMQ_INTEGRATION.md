# Integração RabbitMQ - INUTO GTerra

## Visão Geral

Este documento descreve a integração do RabbitMQ para mensageria assíncrona na plataforma INUTO GTerra, permitindo comunicação eficiente entre nodes e o núcleo central.

## Arquitetura de Mensagens

### Filas Principais

1. **sync.queue** - Sincronização de dados entre nodes
2. **audit.queue** - Logs de auditoria centralizados
3. **offline.queue** - Operações offline pendentes
4. **notification.queue** - Notificações do sistema
5. **workflow.queue** - Eventos de workflow

### Exchanges

1. **sync.exchange** - Exchange para sincronização (topic)
2. **audit.exchange** - Exchange para auditoria (direct)
3. **notification.exchange** - Exchange para notificações (fanout)

## Configuração

### 1. Instalação do Pacote Laravel

```bash
composer require vladimir-yuldashev/laravel-queue-rabbitmq
```

### 2. Configuração (.env)

```env
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

RABBITMQ_QUEUE_SYNC=sync.queue
RABBITMQ_QUEUE_AUDIT=audit.queue
RABBITMQ_QUEUE_OFFLINE=offline.queue
RABBITMQ_QUEUE_NOTIFICATION=notification.queue
RABBITMQ_QUEUE_WORKFLOW=workflow.queue
```

### 3. Configuração (config/queue.php)

```php
'connections' => [
    'rabbitmq' => [
        'driver' => 'rabbitmq',
        'queue' => env('RABBITMQ_QUEUE', 'default'),
        'host' => env('RABBITMQ_HOST', '127.0.0.1'),
        'port' => env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
        'options' => [
            'ssl_options' => [
                'cafile' => env('RABBITMQ_SSL_CAFILE'),
                'local_cert' => env('RABBITMQ_SSL_LOCALCERT'),
                'local_key' => env('RABBITMQ_SSL_LOCALKEY'),
            ],
        ],
        'exchange' => 'amq.direct',
        'exchange_type' => 'direct',
    ],
],
```

## Jobs de Processamento

### 1. Job de Sincronização

```php
<?php

namespace App\Jobs;

use App\Services\SyncService;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public int $tenantId,
        public array $data
    ) {}

    public function handle(SyncService $syncService): void
    {
        $tenant = Tenant::find($this->tenantId);
        
        if (!$tenant) {
            $this->fail(new \Exception('Tenant não encontrado'));
            return;
        }

        try {
            $syncService->syncToCentral($tenant, $this->data);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Sync job failed', [
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### 2. Job de Auditoria

```php
<?php

namespace App\Jobs;

use App\Services\AuditoriaCentralizadaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $action,
        public ?string $entityType,
        public ?int $entityId,
        public ?string $description,
        public ?array $metadata
    ) {}

    public function handle(AuditoriaCentralizadaService $auditService): void
    {
        $auditService->logAction(
            $this->action,
            $this->entityType,
            $this->entityId,
            $this->description,
            $this->metadata
        );
    }
}
```

### 3. Job de Operação Offline

```php
<?php

namespace App\Jobs;

use App\Models\OfflineOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOfflineOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [30, 60, 120, 300, 600];

    public function __construct(
        public int $offlineOperationId
    ) {}

    public function handle(): void
    {
        $operation = OfflineOperation::find($this->offlineOperationId);
        
        if (!$operation) {
            return;
        }

        try {
            $operation->markAsProcessing();
            
            // Processar operação
            match ($operation->operation_type) {
                OfflineOperation::TYPE_CREATE => $this->processCreate($operation),
                OfflineOperation::TYPE_UPDATE => $this->processUpdate($operation),
                OfflineOperation::TYPE_DELETE => $this->processDelete($operation),
            };
            
            $operation->markAsCompleted();
        } catch (\Exception $e) {
            $operation->markAsFailed($e->getMessage());
            $this->fail($e);
        }
    }

    private function processCreate(OfflineOperation $operation): void
    {
        // Implementação específica
    }

    private function processUpdate(OfflineOperation $operation): void
    {
        // Implementação específica
    }

    private function processDelete(OfflineOperation $operation): void
    {
        // Implementação específica
    }
}
```

## Publicação de Mensagens

### 1. Publicar Job de Sincronização

```php
use App\Jobs\SyncJob;

// Publicar job na fila de sincronização
SyncJob::dispatch($tenantId, $data)
    ->onQueue('sync.queue')
    ->delay(now()->addSeconds(5));
```

### 2. Publicar Job de Auditoria

```php
use App\Jobs\AuditLogJob;

// Publicar job na fila de auditoria
AuditLogJob::dispatch(
    'create',
    'Operacao',
    $operacaoId,
    'Operação criada',
    ['tenant_id' => $tenantId]
)->onQueue('audit.queue');
```

### 3. Publicação Direta via Exchange

```php
use Illuminate\Support\Facades\Queue;

Queue::connection('rabbitmq')->pushRaw(
    json_encode([
        'event' => 'tenant.sync',
        'tenant_id' => $tenantId,
        'data' => $syncData,
    ]),
    'sync.queue'
);
```

## Consumo de Mensagens

### 1. Worker de Sincronização

```bash
php artisan queue:work rabbitmq --queue=sync.queue --sleep=3 --tries=3
```

### 2. Worker de Auditoria

```bash
php artisan queue:work rabbitmq --queue=audit.queue --sleep=1 --tries=5
```

### 3. Worker Multi-Queue

```bash
php artisan queue:work rabbitmq --queue=sync.queue,audit.queue,offline.queue --sleep=3
```

## Monitoramento

### 1. Dashboard RabbitMQ Management

Aceder via: `http://localhost:15672`

### 2. Comandos de Monitoramento

```bash
# Ver status das filas
php artisan queue:monitor

# Ver jobs falhados
php artisan queue:failed

# Retentar jobs falhados
php artisan queue:retry all

# Limpar jobs falhados
php artisan queue:flush
```

## Configuração Avançada

### 1. Dead Letter Queue

```php
'connections' => [
    'rabbitmq' => [
        // ... outras configurações
        'options' => [
            'queue_args' => [
                'x-dead-letter-exchange' => 'dlx.exchange',
                'x-dead-letter-routing-key' => 'dlx.routing.key',
            ],
        ],
    ],
],
```

### 2. TTL para Mensagens

```php
SyncJob::dispatch($tenantId, $data)
    ->onQueue('sync.queue')
    ->delay(now()->addMinutes(5))
    ->expireAt(now()->addHours(1));
```

### 3. Prioridade de Filas

```php
SyncJob::dispatch($tenantId, $data)
    ->onQueue('sync.queue')
    ->priority(10); // 1-10, 10 é maior prioridade
```

## Escalabilidade

### 1. Horizontal Scaling

```bash
# Múltiplos workers para a mesma fila
php artisan queue:work rabbitmq --queue=sync.queue &
php artisan queue:work rabbitmq --queue=sync.queue &
php artisan queue:work rabbitmq --queue=sync.queue &
```

### 2. Supervisor Configuration

```ini
[program:sync-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gterra/artisan queue:work rabbitmq --queue=sync.queue --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/gterra/storage/logs/sync-worker.log
```

## Segurança

### 1. Autenticação SSL/TLS

```env
RABBITMQ_SSL_CAFILE=/path/to/ca.pem
RABBITMQ_SSL_LOCALCERT=/path/to/cert.pem
RABBITMQ_SSL_LOCALKEY=/path/to/key.pem
```

### 2. VHosts e Permissões

```bash
# Criar vhost específico
rabbitmqctl add_vhost /gterra

# Criar usuário
rabbitmqctl add_user gterra_user secure_password

# Definir permissões
rabbitmqctl set_permissions -p /gterra gterra_user ".*" ".*" ".*"
```

## Troubleshooting

### 1. Conexão Recusada

```bash
# Verificar se RabbitMQ está a correr
rabbitmqctl status

# Verificar portas
netstat -an | grep 5672
```

### 2. Jobs Acumulando

```bash
# Ver tamanho da fila
rabbitmqctl list_queues name messages

# Limpar fila (cuidado!)
rabbitmqctl purge_queue sync.queue
```

### 3. Memory Leak no Worker

```bash
# Reiniciar workers periodicamente
# Configurar no Supervisor com autorestart
```

## Próximos Passos

1. Implementar retry policies específicas por tipo de job
2. Criar dashboard de monitoramento de filas
3. Implementar alertas para filas com alta latência
4. Otimizar throughput com batch processing
5. Implementar circuit breakers para falhas de conexão
