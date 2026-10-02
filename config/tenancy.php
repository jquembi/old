<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Papel desta instalação
    |--------------------------------------------------------------------------
    |
    | Cada instalação executa apenas um papel. Um node nunca precisa consultar
    | a base central para servir os seus utilizadores; o núcleo nunca abre a
    | base de dados de um node durante uma requisição normal.
    |
    */
    'deployment_mode' => env('TENANT_DEPLOYMENT_MODE', 'node'),
    'primary_domain' => env('TENANT_PRIMARY_DOMAIN', 'gterra.gov.ao'),

    'node' => [
        'id' => (int) env('TENANT_NODE_ID', 0),
        'code' => env('TENANT_NODE_CODE', env('TENANT_DEFAULT', 'HUI')),
        'name' => env('TENANT_NODE_NAME', 'Node Provincial'),
        'database' => env('TENANT_NODE_DATABASE', env('DB_DATABASE', database_path('database.sqlite'))),
        'active' => env('TENANT_NODE_ACTIVE', true),
    ],
    /*
    |--------------------------------------------------------------------------
    | Tenant Default
    |--------------------------------------------------------------------------
    |
    | Tenant padrão a ser usado quando não for possível resolver o tenant
    | através de subdomínio, header ou utilizador autenticado.
    |
    */
    'default_tenant' => env('TENANT_DEFAULT', 'CTR'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    |
    | Métodos de resolução de tenant, em ordem de prioridade.
    | Os métodos disponíveis são: subdomain, header, query, user, parameter.
    |
    */
    'resolution_methods' => [
        'subdomain',
        'header',
        'query',
        'user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subdomain Domain
    |--------------------------------------------------------------------------
    |
    | Domínio base para resolução por subdomínio.
    | Exemplo: gterra.ao
    |
    */
    'subdomain_domain' => env('TENANT_SUBDOMAIN_DOMAIN', env('TENANT_PRIMARY_DOMAIN', 'gterra.gov.ao')),

    /*
    |--------------------------------------------------------------------------
    | Header Name
    |--------------------------------------------------------------------------
    |
    | Nome do header HTTP usado para passar o código do tenant.
    |
    */
    'header_name' => env('TENANT_HEADER_NAME', 'X-Tenant'),

    /*
    |--------------------------------------------------------------------------
    | Query Parameter
    |--------------------------------------------------------------------------
    |
    | Nome do parâmetro de query string usado para passar o código do tenant.
    |
    */
    'query_parameter' => env('TENANT_QUERY_PARAMETER', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware a ser aplicado para rotas que requerem resolução de tenant.
    |
    */
    'middleware' => [
        'web' => ['set.tenant'],
        'api' => ['set.tenant', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Configuração de conexão de base de dados para tenants.
    |
    */
    'database' => [
        'connection_name' => 'tenant',
        // A ligação central guarda o registo de nodes, indicadores e auditoria.
        // A aplicação nunca deve usar uma ligação provincial como fallback.
        'central_connection' => env('TENANT_CENTRAL_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'node_connection' => env('TENANT_NODE_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'prefix' => env('DB_PREFIX', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configuração de cache para tenants.
    |
    */
    'cache' => [
        'enabled' => env('TENANT_CACHE_ENABLED', true),
        'ttl' => env('TENANT_CACHE_TTL', 3600), // 1 hora
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configuração de rotas por tipo de tenant.
    |
    */
    'routes' => [
        'central' => [
            'prefix' => 'nacional',
            'middleware' => ['tenant.type:central'],
        ],
        'provincial' => [
            'prefix' => '',
            'middleware' => ['tenant.type:provincial'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração de sincronização entre tenants.
    |
    */
    'sync' => [
        'enabled' => env('TENANT_SYNC_ENABLED', true),
        'interval_minutes' => env('TENANT_SYNC_INTERVAL', 30),
        'retry_attempts' => env('TENANT_SYNC_RETRY', 3),
        'batch_size' => env('TENANT_SYNC_BATCH_SIZE', 100),
        // Apenas tabelas que possuem sync_id, sync_status e sync_version podem
        // ser usadas pelo gateway. Nunca aceite o nome de tabela vindo do cliente.
        'entities' => [
            'operacoes',
            'instrumentos',
            'loteamentos',
            'croquis',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
        ],
        'central_url' => rtrim((string) env('TENANT_CENTRAL_URL', 'https://gterra.gov.ao'), '/'),
        'token' => env('TENANT_SYNC_TOKEN'),
        'connect_timeout' => (int) env('TENANT_SYNC_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('TENANT_SYNC_TIMEOUT', 10),
        'backoff_minutes' => (int) env('TENANT_SYNC_BACKOFF_MINUTES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Offline Mode
    |--------------------------------------------------------------------------
    |
    | Configuração de funcionamento offline.
    |
    */
    'offline' => [
        'enabled' => env('TENANT_OFFLINE_ENABLED', true),
        'queue_name' => env('TENANT_OFFLINE_QUEUE', 'offline-operations'),
        'max_offline_hours' => env('TENANT_MAX_OFFLINE_HOURS', 24),
    ],
];
