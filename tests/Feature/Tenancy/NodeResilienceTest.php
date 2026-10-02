<?php

use App\Http\Middleware\SetTenant;
use App\Models\NodeSyncOutbox;
use App\Services\Sync\NodeSyncOutboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config([
        'tenancy.deployment_mode' => 'node',
        'tenancy.node.code' => 'HUI',
        'tenancy.node.name' => 'Província da Huíla',
        'tenancy.node.database' => ':memory:',
        'tenancy.database.node_connection' => 'sqlite',
        'tenancy.sync.enabled' => true,
        'tenancy.sync.central_url' => 'https://gterra.gov.ao',
        'tenancy.sync.token' => 'node-token',
        'tenancy.sync.backoff_minutes' => 1,
    ]);
});

test('um node resolve o seu contexto sem consultar a ligação central', function () {
    config(['tenancy.database.central_connection' => 'inexistente']);

    $request = Request::create('http://huila.gterra.gov.ao/');
    $response = app(SetTenant::class)->handle($request, function () {
        return response()->json(['tenant' => app('current.tenant')->code]);
    });

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true)['tenant'])->toBe('HUI');
});

test('falha do núcleo mantém a sincronização na caixa de saída do node', function () {
    Http::fake([
        'https://gterra.gov.ao/*' => Http::response(['message' => 'indisponível'], 503),
    ]);

    $service = app(NodeSyncOutboxService::class);
    $outbox = $service->enqueue(['croquis' => [['sync_id' => 'node-1', 'sync_version' => 1]]]);

    expect($service->deliverReady())->toBe(['sent' => 0, 'failed' => 1]);

    $outbox->refresh();
    expect($outbox->status)->toBe('pending')
        ->and($outbox->attempts)->toBe(1)
        ->and($outbox->next_attempt_at)->not->toBeNull()
        ->and(NodeSyncOutbox::count())->toBe(1);
});
