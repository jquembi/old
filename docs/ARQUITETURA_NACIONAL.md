# ARQUITETURA NACIONAL INUTO GTERRA
## Plataforma Nacional Integrada de Gestão de Terras e Administração

**Versão:** 1.0  
**Data:** 28 Agosto 2026  
**Status:** Planeamento Arquitetural

---

## 1. VISÃO GERAL

### 1.1 Princípio Fundamental

**UMA PLATAFORMA → UM CÓDIGO → UMA ARQUITETURA → 21 OPERAÇÕES PROVINCIAIS + 1 GOVERNAÇÃO NACIONAL**

O sistema INUTO GTerra será implementado como um único projeto Laravel monorepo com arquitetura modular capaz de suportar:

- **1 Núcleo Central Nacional** - Governança, supervisão, indicadores consolidados
- **21 Nodes Provinciais** - Operação autónoma por província
- **1 ERP Integrado** - Financeiro, RH, Compras, Património
- **1 Camada de Integração** - API Gateway, sincronização, mensageria
- **1 Aplicação Web** - Interface unificada
- **1 Aplicação Mobile** - Fiscalização e vistorias
- **GIS/BI** - Georreferenciamento e analytics

### 1.2 Arquitetura Lógica

```
┌─────────────────────────────────────────────────────────────┐
│                    NÚCLEO CENTRAL NACIONAL                   │
│  (Módulo Nacional - Governança e Supervisão)                 │
│                                                              │
│  • Dashboard Nacional                                        │
│  • Indicadores Consolidados                                  │
│  • Administração Nacional                                    │
│  • Auditoria Centralizada                                    │
│  • Relatórios Nacionais                                      │
│  • Supervisão de Nodes                                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         │   CAMADA DE INTEGRAÇÃO    │
         │                           │
         │  • API Gateway            │
         │  • Sincronização          │
         │  • Event Bus              │
         │  • Queue Manager          │
         └─────────────┬─────────────┘
                       │
    ┌──────────────────┼──────────────────┐
    │                  │                  │
┌───▼────┐      ┌─────▼─────┐      ┌────▼────┐
│ NODE  │      │   NODE    │      │  NODE   │
│ HUILA  │      │ PROVÍNCIA │      │ PROV.21 │
│        │      │    02     │      │         │
└───┬────┘      └─────┬─────┘      └────┬────┘
    │                 │                 │
┌───▼────┐      ┌─────▼─────┐      ┌────▼────┐
│  BD    │      │    BD     │      │   BD    │
│ Local  │      │  Local    │      │  Local  │
└───┬────┘      └─────┬─────┘      └────┬────┘
    │                 │                 │
┌───▼────┐      ┌─────▼─────┐      ┌────▼────┐
│  ERP   │      │   ERP     │      │  ERP    │
│ Local  │      │  Local    │      │  Local  │
└────────┘      └───────────┘      └─────────┘
```

---

## 2. ESTRATÉGIA DE MULTITENANCY

### 2.1 Abordagem: Database per Tenant

Cada província (Node) terá a sua própria base de dados, garantindo:

- **Isolamento completo** de dados
- **Performance otimizada** por node
- **Backup independente** por província
- **Recuperação de desastres** granular
- **Escalabilidade individual** por província

### 2.2 Estrutura de Tenants

```php
// config/tenancy.php
return [
    'default' => 'central',
    'tenants' => [
        'central' => [
            'name' => 'Núcleo Central Nacional',
            'type' => 'central',
            'database' => env('DB_DATABASE', 'gterra_central'),
        ],
        'huila' => [
            'name' => 'Província da Huíla',
            'type' => 'provincial',
            'code' => 'HUI',
            'database' => 'gterra_huila',
            'active' => true,
        ],
        'bengo' => [
            'name' => 'Província do Bengo',
            'type' => 'provincial',
            'code' => 'BGO',
            'database' => 'gterra_bengo',
            'active' => false, // A ativar quando necessário
        ],
        // ... 19 províncias adicionais
    ],
];
```

### 2.3 Middleware de Tenant Resolution

```php
// app/Http/Middleware/SetTenant.php
class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);
        
        if (!$tenant) {
            abort(404, 'Tenant não encontrado');
        }

        // Set tenant connection
        Config::set('database.default', $tenant->database);
        
        // Store tenant in context
        app()->instance('current.tenant', $tenant);
        
        return $next($request);
    }

    private function resolveTenant(Request $request)
    {
        // 1. Verificar subdomínio (huila.gterra.ao)
        if ($subdomain = $this->getSubdomain($request)) {
            return Tenant::where('code', strtoupper($subdomain))->first();
        }

        // 2. Verificar header X-Tenant
        if ($request->hasHeader('X-Tenant')) {
            return Tenant::where('code', $request->header('X-Tenant'))->first();
        }

        // 3. Verificar utilizador autenticado
        if (auth()->check() && auth()->user()->provincia_id) {
            return Tenant::where('provincia_id', auth()->user()->provincia_id)->first();
        }

        // 4. Default para central
        return Tenant::where('type', 'central')->first();
    }
}
```

### 2.4 Rotas por Tenant

```php
// routes/web.php
Route::middleware(['set.tenant'])->group(function () {
    // Rotas do Núcleo Central
    Route::middleware(['tenant.type:central'])->group(function () {
        Route::get('/nacional/dashboard', NacionalDashboard::class);
        Route::get('/nacional/indicadores', NacionalIndicadores::class);
        Route::get('/nacional/nodes', NodesSupervisao::class);
    });

    // Rotas dos Nodes Provinciais
    Route::middleware(['tenant.type:provincial'])->group(function () {
        Route::get('/dashboard', Dashboard::class);
        Route::get('/municipios', ListaMunicipios::class);
        // ... rotas existentes da Huíla
    });
});
```

---

## 3. MODELO DE DADOS UNIFICADO

### 3.1 Tabelas Centrais (Núcleo Nacional)

```sql
-- tenants
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(3) UNIQUE NOT NULL, -- HUI, BGO, LUA...
    name VARCHAR(255) NOT NULL,
    type ENUM('central', 'provincial') NOT NULL,
    database_name VARCHAR(255) NOT NULL,
    database_host VARCHAR(255),
    database_port INT,
    is_active BOOLEAN DEFAULT true,
    sync_status ENUM('online', 'offline', 'syncing') DEFAULT 'online',
    last_sync_at TIMESTAMP NULL,
    config JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- national_indicators
CREATE TABLE national_indicators (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    indicator_type VARCHAR(100) NOT NULL,
    value JSON NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    synced_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

-- sync_logs
CREATE TABLE sync_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    source_tenant_id BIGINT NOT NULL,
    target_tenant_id BIGINT,
    sync_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'success', 'failed', 'partial') NOT NULL,
    records_count INT DEFAULT 0,
    error_message TEXT,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    metadata JSON,
    FOREIGN KEY (source_tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (target_tenant_id) REFERENCES tenants(id)
);

-- national_parameters
CREATE TABLE national_parameters (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    parameter_key VARCHAR(255) UNIQUE NOT NULL,
    parameter_value TEXT NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    updated_by BIGINT,
    updated_at TIMESTAMP
);
```

### 3.2 Tabelas por Tenant (Provinciais)

As tabelas existentes serão mantidas em cada base de dados provincial:

- `users` (com tenant_id implicito pela BD)
- `provincias` (apenas a província do tenant)
- `municipios` (municípios da província)
- `roles` (com escopo provincial)
- `operacoes`, `instrumentos`, `loteamentos`, etc.
- `map_layers`, `map_features` (GIS)
- `auditoria`, `log_acesso`

### 3.3 Campos de Sincronização

Todas as tabelas principais terão campos para controlo de sincronização:

```sql
ALTER TABLE operacoes ADD COLUMN sync_id VARCHAR(36) UNIQUE;
ALTER TABLE operacoes ADD COLUMN sync_status ENUM('pending', 'synced', 'conflict') DEFAULT 'synced';
ALTER TABLE operacoes ADD COLUMN sync_version INT DEFAULT 1;
ALTER TABLE operacoes ADD COLUMN synced_at TIMESTAMP NULL;
ALTER TABLE operacoes ADD COLUMN sync_error TEXT;
```

---

## 4. CAMADA DE INTEGRAÇÃO

### 4.1 API Gateway

```php
// app/Http/Controllers/Api/ApiGatewayController.php
class ApiGatewayController extends Controller
{
    public function proxy(Request $request, $service, $path)
    {
        $tenant = $this->resolveTargetTenant($request);
        $serviceConfig = $this->getServiceConfig($service);

        // Rate limiting por tenant
        $this->applyRateLimit($tenant, $service);

        // Autenticação e autorização
        $this->authenticateRequest($request);
        $this->authorizeRequest($request, $service, $path);

        // Proxy para o serviço alvo
        $response = Http::withToken($request->bearerToken())
            ->withHeaders($this->buildHeaders($request))
            ->send(
                $request->method(),
                $this->buildTargetUrl($tenant, $serviceConfig, $path),
                [
                    'body' => $request->getContent(),
                    'query' => $request->query(),
                ]
            );

        return $this->buildGatewayResponse($response);
    }
}
```

### 4.2 Sistema de Sincronização

```php
// app/Services/Sync/SyncService.php
class SyncService
{
    public function syncToCentral(Tenant $tenant, array $data)
    {
        $syncLog = SyncLog::create([
            'source_tenant_id' => $tenant->id,
            'target_tenant_id' => Tenant::where('type', 'central')->first()->id,
            'sync_type' => 'push_to_central',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            DB::connection($tenant->database_name)->transaction(function () use ($data, $syncLog) {
                foreach ($data as $entity => $records) {
                    $this->syncEntity($entity, $records, $syncLog);
                }
            });

            $syncLog->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            return $syncLog;
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    private function syncEntity(string $entity, array $records, SyncLog $syncLog)
    {
        foreach ($records as $record) {
            $centralRecord = DB::connection('central')
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
                DB::connection('central')
                    ->table($entity)
                    ->where('sync_id', $record['sync_id'])
                    ->update($record);
            } else {
                // Insert
                DB::connection('central')->table($entity)->insert($record);
            }
        }

        $syncLog->increment('records_count', count($records));
    }
}
```

### 4.3 Event Bus (Mensageria)

```php
// app/Events/NodeEvent.php
class NodeEvent implements ShouldDispatch
{
    public function __construct(
        public string $eventType,
        public Tenant $sourceTenant,
        public array $payload,
        public ?Tenant $targetTenant = null
    ) {}

    public function broadcastOn()
    {
        return [
            new Channel('nodes.' . $this->sourceTenant->code),
            new Channel('central'),
        ];
    }

    public function broadcastAs()
    {
        return $this->eventType;
    }
}

// Eventos disponíveis
const PROCESSO_CRIADO = 'processo.criado';
const PROCESSO_APROVADO = 'processo.aprovado';
const TERRENO_ATUALIZADO = 'terreno.atualizado';
const DOCUMENTO_EMITIDO = 'documento.emitido';
const PAGAMENTO_REALIZADO = 'pagamento.realizado';
const UTILIZADOR_CRIADO = 'utilizador.criado';
const NODE_ONLINE = 'node.online';
const NODE_OFFLINE = 'node.offline';
const SINCRONIZACAO_CONCLUIDA = 'sincronizacao.concluida';
```

### 4.4 Funcionamento Offline

```php
// app/Services/Offline/OfflineQueueService.php
class OfflineQueueService
{
    public function queueOperation(array $operation)
    {
        OfflineOperation::create([
            'tenant_id' => current_tenant()->id,
            'operation_type' => $operation['type'],
            'payload' => $operation['data'],
            'status' => 'pending',
            'priority' => $operation['priority'] ?? 'normal',
        ]);
    }

    public function processQueue(Tenant $tenant)
    {
        $operations = OfflineOperation::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        foreach ($operations as $operation) {
            try {
                $this->executeOperation($operation);
                $operation->update(['status' => 'completed']);
            } catch (\Exception $e) {
                $operation->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => $operation->retry_count + 1,
                ]);
            }
        }
    }

    private function executeOperation(OfflineOperation $operation)
    {
        switch ($operation->operation_type) {
            case 'create':
                $this->executeCreate($operation);
                break;
            case 'update':
                $this->executeUpdate($operation);
                break;
            case 'delete':
                $this->executeDelete($operation);
                break;
        }
    }
}
```

---

## 5. EXPANSÃO DO SISTEMA DE PERMISSÕES

### 5.1 Novos Níveis Hierárquicos

```php
// app/Models/Role.php
class Role extends Model
{
    // Nível 0: Super Admin Nacional
    public const SUPER_ADMIN_NACIONAL = 'super_admin_nacional';
    public const NIVEL_NACIONAL = 0;

    // Nível 1: Admin Nacional
    public const ADMIN_NACIONAL = 'admin_nacional';
    public const GESTOR_NACIONAL = 'gestor_nacional';
    public const AUDITOR_NACIONAL = 'auditor_nacional';
    public const NIVEL_ADMIN_NACIONAL = 1;

    // Nível 2: INOTU Central (existente)
    public const CHEFE_SECCAO_TECNICA = 'chefe_seccao_tecnica';
    public const DIRECTOR_INOTU = 'director_inotu';
    public const NIVEL_INOTU = 2;

    // Nível 3: Governo Provincial (existente)
    public const GOVERNADOR = 'governador';
    public const NIVEL_PROVINCIAL = 3;

    // Nível 4: Administração Municipal (existente)
    public const ADMIN_MUNICIPAL = 'admin_municipal';
    public const NIVEL_MUNICIPAL = 4;

    // Nível 5: Técnicos (existente)
    public const TECNICO_ORDENAMENTO = 'tecnico_ordenamento';
    public const NIVEL_TECNICO = 5;

    // Novos perfis para ERP
    public const FINANCEIRO = 'financeiro';
    public const RH = 'rh';
    public const COMPRAS = 'compras';
    public const PATRIMONIO = 'patrimonio';
    public const CONSULTA = 'consulta';
}
```

### 5.2 Escopo de Permissões

```php
// app/Concerns/TemPermissoes.php
trait TemPermissoes
{
    public function temPermissao(string $permissao, ?string $escopo = null): bool
    {
        // Super Admin Nacional tem tudo
        if ($this->isSuperAdminNacional()) {
            return true;
        }

        // Verificar permissão no role
        $hasPermission = $this->roles()
            ->whereHas('permissoes', function ($query) use ($permissao) {
                $query->where('nome', $permissao);
            })
            ->exists();

        if (!$hasPermission) {
            return false;
        }

        // Verificar escopo se fornecido
        if ($escopo) {
            return $this->verificarEscopo($escopo);
        }

        return true;
    }

    private function verificarEscopo(string $escopo): bool
    {
        switch ($escopo) {
            case 'nacional':
                return $this->isNacional();
            case 'provincial':
                return $this->isProvincial() || $this->isNacional();
            case 'municipal':
                return $this->isMunicipal() || $this->isProvincial() || $this->isNacional();
            default:
                return true;
        }
    }

    public function isSuperAdminNacional(): bool
    {
        return $this->roles()->where('nome', self::SUPER_ADMIN_NACIONAL)->exists();
    }

    public function isNacional(): bool
    {
        return $this->roles()->whereIn('nivel', [
            self::NIVEL_NACIONAL,
            self::NIVEL_ADMIN_NACIONAL,
        ])->exists();
    }
}
```

### 5.3 Novas Permissões

```php
// Permissões Nacionais
'indicadores.nacional.ver',
'indicadores.nacional.exportar',
'nodes.supervisionar',
'nodes.ativar',
'nodes.desativar',
'parametros.nacionais.gerir',
'relatorios.nacionais.ver',

// Permissões de ERP
'erp.financeiro.ver',
'erp.financeiro.editar',
'erp.rh.ver',
'erp.rh.editar',
'erp.compras.ver',
'erp.compras.editar',
'erp.patrimonio.ver',
'erp.patrimonio.editar',

// Permissões de Sincronização
'sync.configurar',
'sync.executar',
'sync.logs.ver',
```

---

## 6. MÓDULO ERP INTEGRADO

### 6.1 Estrutura de Módulos ERP

```
app/Modules/Erp/
├── Financeiro/
│   ├── Models/
│   │   ├── PlanoContas.php
│   │   ├── ContaPagar.php
│   │   ├── ContaReceber.php
│   │   ├── Tesouraria.php
│   │   └── Pagamento.php
│   ├── Livewire/
│   │   ├── GestaoContasPagar.php
│   │   ├── GestaoContasReceber.php
│   │   └── TesourariaDashboard.php
│   └── Services/
│       └── FinanceiroService.php
├── Rh/
│   ├── Models/
│   │   ├── Funcionario.php
│   │   ├── Departamento.php
│   │   ├── Cargo.php
│   │   ├── Presenca.php
│   │   └── Ferias.php
│   └── Livewire/
│       └── GestaoFuncionarios.php
├── Compras/
│   ├── Models/
│   │   ├── Requisicao.php
│   │   ├── Fornecedor.php
│   │   ├── OrdemCompra.php
│   │   └── Contrato.php
│   └── Livewire/
│       └── GestaoCompras.php
└── Patrimonio/
    ├── Models/
    │   ├── Bem.php
    │   ├── Equipamento.php
    │   ├── Viatura.php
    │   └── Inventario.php
    └── Livewire/
        └── GestaoPatrimonio.php
```

### 6.2 Modelo Financeiro (Exemplo)

```php
// app/Modules/Erp/Financeiro/Models/PlanoContas.php
namespace App\Modules\Erp\Financeiro\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoContas extends Model
{
    protected $table = 'erp_plano_contas';

    protected $fillable = [
        'codigo',
        'designacao',
        'tipo', // receita, despesa, ativo, passivo
        'nivel', // 1-5 para hierarquia
        'conta_pai_id',
        'activo',
    ];

    public function filhos()
    {
        return $this->hasMany(PlanoContas::class, 'conta_pai_id');
    }

    public function contaPai()
    {
        return $this->belongsTo(PlanoContas::class, 'conta_pai_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(Lancamento::class, 'conta_id');
    }
}

// app/Modules/Erp/Financeiro/Models/ContaPagar.php
class ContaPagar extends Model
{
    protected $table = 'erp_contas_pagar';

    protected $fillable = [
        'numero',
        'fornecedor_id',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status', // pendente, paga, cancelada
        'categoria_id',
        'descricao',
        'anexo_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }
}
```

### 6.3 Rotas ERP

```php
// routes/erp.php
Route::middleware(['auth', 'verified', 'set.tenant'])->group(function () {
    // Financeiro
    Route::prefix('erp/financeiro')->group(function () {
        Route::get('/contas-pagar', GestaoContasPagar::class)
            ->middleware('permissao:erp.financeiro.ver')
            ->name('erp.financeiro.contas-pagar');
        
        Route::get('/contas-receber', GestaoContasReceber::class)
            ->middleware('permissao:erp.financeiro.ver')
            ->name('erp.financeiro.contas-receber');
        
        Route::get('/tesouraria', TesourariaDashboard::class)
            ->middleware('permissao:erp.financeiro.ver')
            ->name('erp.financeiro.tesouraria');
    });

    // RH
    Route::prefix('erp/rh')->group(function () {
        Route::get('/funcionarios', GestaoFuncionarios::class)
            ->middleware('permissao:erp.rh.ver')
            ->name('erp.rh.funcionarios');
    });

    // Compras
    Route::prefix('erp/compras')->group(function () {
        Route::get('/requisicoes', GestaoCompras::class)
            ->middleware('permissao:erp.compras.ver')
            ->name('erp.compras.requisicoes');
    });

    // Património
    Route::prefix('erp/patrimonio')->group(function () {
        Route::get('/bens', GestaoPatrimonio::class)
            ->middleware('permissao:erp.patrimonio.ver')
            ->name('erp.patrimonio.bens');
    });
});
```

---

## 7. MOTOR DE WORKFLOWS

### 7.1 Modelo de Workflow

```php
// app/Models/Workflow.php
class Workflow extends Model
{
    protected $fillable = [
        'nome',
        'tipo_processo',
        'descricao',
        'configuracao', // JSON com definição de estados e transições
        'activo',
    ];

    protected $casts = [
        'configuracao' => 'array',
    ];

    public function processos()
    {
        return $this->hasMany(ProcessoWorkflow::class);
    }
}

// app/Models/ProcessoWorkflow.php
class ProcessoWorkflow extends Model
{
    protected $fillable = [
        'processo_id',
        'processo_type', // Operacao, TerrenoOcioso, etc.
        'workflow_id',
        'estado_actual',
        'dados', // JSON com dados específicos do processo
        'iniciado_por',
        'iniciado_em',
        'concluido_em',
    ];

    protected $casts = [
        'dados' => 'array',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    public function transicoes()
    {
        return $this->hasMany(WorkflowTransicao::class);
    }

    public function podeTransitar(string $novoEstado): bool
    {
        $config = $this->workflow->configuracao;
        $transicoesPermitidas = $config['estados'][$this->estado_actual]['transicoes'] ?? [];

        return in_array($novoEstado, $transicoesPermitidas);
    }

    public function transitar(string $novoEstado, array $dados = [], ?User $usuario = null)
    {
        if (!$this->podeTransitar($novoEstado)) {
            throw new \Exception('Transição não permitida');
        }

        $transicao = $this->transicoes()->create([
            'de_estado' => $this->estado_actual,
            'para_estado' => $novoEstado,
            'realizado_por' => $usuario?->id ?? auth()->id(),
            'dados' => $dados,
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        $this->update([
            'estado_actual' => $novoEstado,
            'dados' => array_merge($this->dados, $dados),
        ]);

        // Verificar se é estado final
        $config = $this->workflow->configuracao;
        if ($config['estados'][$novoEstado]['final'] ?? false) {
            $this->update(['concluido_em' => now()]);
        }

        // Disparar evento
        event(new WorkflowTransicaoEvent($this, $transicao));

        return $transicao;
    }
}
```

### 7.2 Workflow Configurável

```php
// Exemplo de configuração de workflow para processos fundiários
$workflowConfig = [
    'estados' => [
        'rececao' => [
            'nome' => 'Receção',
            'descricao' => 'Processo recebido',
            'transicoes' => ['validacao'],
            'final' => false,
            'responsavel' => 'secretaria',
        ],
        'validacao' => [
            'nome' => 'Validação',
            'descricao' => 'Validação documental',
            'transicoes' => ['analise', 'devolucao'],
            'final' => false,
            'responsavel' => 'tecnico',
        ],
        'analise' => [
            'nome' => 'Análise',
            'descricao' => 'Análise técnica',
            'transicoes' => ['vistoria', 'parecer'],
            'final' => false,
            'responsavel' => 'tecnico',
        ],
        'vistoria' => [
            'nome' => 'Vistoria',
            'descricao' => 'Vistoria in loco',
            'transicoes' => ['parecer'],
            'final' => false,
            'responsavel' => 'fiscal',
        ],
        'parecer' => [
            'nome' => 'Parecer',
            'descricao' => 'Emissão de parecer',
            'transicoes' => ['aprovacao', 'rejeicao'],
            'final' => false,
            'responsavel' => 'tecnico',
        ],
        'aprovacao' => [
            'nome' => 'Aprovação',
            'descricao' => 'Aprovação final',
            'transicoes' => ['pagamento'],
            'final' => false,
            'responsavel' => 'director',
        ],
        'pagamento' => [
            'nome' => 'Pagamento',
            'descricao' => 'Pagamento de taxas',
            'transicoes' => ['emissao'],
            'final' => false,
            'responsavel' => 'financeiro',
        ],
        'emissao' => [
            'nome' => 'Emissão',
            'descricao' => 'Emissão de documento',
            'transicoes' => ['arquivo'],
            'final' => false,
            'responsavel' => 'secretaria',
        ],
        'arquivo' => [
            'nome' => 'Arquivo',
            'descricao' => 'Processo arquivado',
            'transicoes' => [],
            'final' => true,
            'responsavel' => 'arquivo',
        ],
    ],
];
```

---

## 8. GIS AVANÇADO COM POSTGIS

### 8.1 Migração para PostgreSQL + PostGIS

```php
// database/migrations/xxxx_xx_xx_add_postgis_to_map_features.php
public function up()
{
    // Alterar tipo de coluna para geometry
    DB::statement('ALTER TABLE map_features ALTER COLUMN geometry TYPE geometry(Geometry,4326) USING ST_GeomFromText(geometry)');
    
    // Adicionar índices espaciais
    DB::statement('CREATE INDEX map_features_geometry_idx ON map_features USING GIST (geometry)');
    
    // Adicionar colunas PostGIS
    Schema::table('map_features', function (Blueprint $table) {
        $table->decimal('area_m2', 15, 2)->nullable();
        $table->decimal('perimetro_m', 15, 2)->nullable();
        $table->point('centroid')->nullable();
    });
}
```

### 8.2 Consultas Espaciais

```php
// app/Services/Gis/GisService.php
class GisService
{
    public function calcularArea(MapFeature $feature): float
    {
        return DB::selectOne(
            'SELECT ST_Area(geometry::geography) as area FROM map_features WHERE id = ?',
            [$feature->id]
        )->area;
    }

    public function intersectarMunicipio(MapFeature $feature, Municipio $municipio): bool
    {
        return DB::selectOne(
            'SELECT ST_Intersects(
                (SELECT geometry FROM map_features WHERE id = ?),
                (SELECT geometry FROM municipios WHERE id = ?)
            ) as intersects',
            [$feature->id, $municipio->id]
        )->intersects;
    }

    public function featuresProximas(MapFeature $feature, float $raioMetros): Collection
    {
        return DB::select(
            'SELECT f.*, ST_Distance(f.geometry::geography, ?::geography) as distancia
             FROM map_features f
             WHERE ST_DWithin(f.geometry::geography, ?::geography, ?)
             ORDER BY distancia',
            [$feature->geometry, $feature->geometry, $raioMetros]
        );
    }

    public function exportarGeoJSON(Collection $features): string
    {
        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => $features->map(function ($feature) {
                return [
                    'type' => 'Feature',
                    'geometry' => json_decode(DB::selectOne('SELECT ST_AsGeoJSON(geometry) as geojson FROM map_features WHERE id = ?', [$feature->id])->geojson),
                    'properties' => $feature->toArray(),
                ];
            })->toArray(),
        ];

        return json_encode($geoJson);
    }
}
```

---

## 9. NÚCLEO CENTRAL NACIONAL

### 9.1 Dashboard Nacional

```php
// app/Livewire/Nacional/NacionalDashboard.php
class NacionalDashboard extends Component
{
    public function mount()
    {
        $this->loadIndicadores();
    }

    private function loadIndicadores()
    {
        $this->totalProcessos = NationalIndicator::sum('value->total_processos');
        $this->totalTerras = NationalIndicator::sum('value->total_terras');
        $this->totalReceitas = NationalIndicator::sum('value->total_receitas');
        $this->nodesOnline = Tenant::where('sync_status', 'online')->count();
        $this->nodesOffline = Tenant::where('sync_status', 'offline')->count();
    }

    public function render()
    {
        return view('livewire.nacional.nacional-dashboard');
    }
}
```

### 9.2 Supervisão de Nodes

```php
// app/Livewire/Nacional/NodesSupervisao.php
class NodesSupervisao extends Component
{
    public Collection $nodes;

    public function mount()
    {
        $this->nodes = Tenant::where('type', 'provincial')
            ->with(['latestSyncLog'])
            ->get();
    }

    public function ativarNode(Tenant $node)
    {
        $node->update(['is_active' => true]);
        
        event(new NodeEvent(
            NodeEvent::NODE_ONLINE,
            $node,
            ['ativado_por' => auth()->user()->name]
        ));

        $this->dispatch('node-ativado');
    }

    public function forcarSincronizacao(Tenant $node)
    {
        dispatch(new SyncNodeJob($node));
        
        $this->dispatch('sincronizacao-iniciada');
    }
}
```

### 9.3 Relatórios Nacionais

```php
// app/Services/Nacional/RelatorioNacionalService.php
class RelatorioNacionalService
{
    public function gerarRelatorioProcessos(Carbon $inicio, Carbon $fim): array
    {
        $dados = [];

        foreach (Tenant::where('type', 'provincial')->where('is_active', true)->get() as $tenant) {
            $processos = DB::connection($tenant->database_name)
                ->table('operacoes')
                ->whereBetween('created_at', [$inicio, $fim])
                ->count();

            $dados[] = [
                'provincia' => $tenant->name,
                'processos' => $processos,
            ];
        }

        return [
            'periodo' => [
                'inicio' => $inicio->format('Y-m-d'),
                'fim' => $fim->format('Y-m-d'),
            ],
            'dados' => $dados,
            'total' => array_sum(array_column($dados, 'processos')),
        ];
    }

    public function exportarPdf(array $dados): string
    {
        $pdf = PDF::loadView('relatorios.nacional.processos', compact('dados'));
        
        $path = storage_path('app/relatorios/nacional/processos_' . now()->format('Y-m-d_His') . '.pdf');
        $pdf->save($path);

        return $path;
    }
}
```

---

## 10. INFRAESTRUTURA E DEPLOYMENT

### 10.1 Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  # Núcleo Central
  central-app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: gterra-central
    environment:
      - APP_ENV=production
      - DB_HOST=central-db
      - DB_DATABASE=gterra_central
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - central-db
      - redis
    networks:
      - gterra-network

  central-db:
    image: postgres:15
    container_name: gterra-central-db
    environment:
      - POSTGRES_DB=gterra_central
      - POSTGRES_USER=gterra
      - POSTGRES_PASSWORD=secret
    volumes:
      - central-db-data:/var/lib/postgresql/data
    networks:
      - gterra-network

  # Node Huíla (exemplo)
  huila-app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: gterra-huila
    environment:
      - APP_ENV=production
      - DB_HOST=huila-db
      - DB_DATABASE=gterra_huila
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
      - TENANT_CODE=HUI
    depends_on:
      - huila-db
      - redis
    networks:
      - gterra-network

  huila-db:
    image: postgis/postgis:15-3.3
    container_name: gterra-huila-db
    environment:
      - POSTGRES_DB=gterra_huila
      - POSTGRES_USER=gterra
      - POSTGRES_PASSWORD=secret
    volumes:
      - huila-db-data:/var/lib/postgresql/data
    networks:
      - gterra-network

  # Serviços Compartilhados
  redis:
    image: redis:7-alpine
    container_name: gterra-redis
    networks:
      - gterra-network

  rabbitmq:
    image: rabbitmq:3-management
    container_name: gterra-rabbitmq
    environment:
      - RABBITMQ_DEFAULT_USER=gterra
      - RABBITMQ_DEFAULT_PASS=secret
    ports:
      - "5672:5672"
      - "15672:15672"
    networks:
      - gterra-network

  nginx:
    image: nginx:alpine
    container_name: gterra-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - central-app
    networks:
      - gterra-network

volumes:
  central-db-data:
  huila-db-data:

networks:
  gterra-network:
    driver: bridge
```

### 10.2 CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy INUTO GTerra

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install --no-progress --no-interaction
      - name: Run Tests
        run: composer test

  build:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Build Docker Images
        run: |
          docker build -t gterra-central:latest .
          docker tag gterra-central:latest registry.example.com/gterra-central:latest
      - name: Push Images
        run: |
          docker push registry.example.com/gterra-central:latest

  deploy-central:
    needs: build
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Central
        run: |
          kubectl set image deployment/gterra-central gterra-central=registry.example.com/gterra-central:latest

  deploy-nodes:
    needs: build
    runs-on: ubuntu-latest
    strategy:
      matrix:
        node: [huila, bengo, ...]
    steps:
      - name: Deploy to ${{ matrix.node }}
        run: |
          kubectl set image deployment/gterra-${{ matrix.node }} gterra-app=registry.example.com/gterra-central:latest
```

---

## 11. PLANO DE IMPLEMENTAÇÃO

### FASE 1: Diagnóstico e Arquitetura (2 semanas)
- [x] Análise do sistema atual da Huíla
- [ ] Documentação completa de funcionalidades existentes
- [ ] Desenho da arquitetura multi-node
- [ ] Definição do modelo de dados unificado
- [ ] Estratégia de segurança e auditoria

### FASE 2: Núcleo da Plataforma (4 semanas)
- [ ] Implementação de multi-tenancy
- [ ] Refatoração de autenticação
- [ ] Expansão do sistema de permissões
- [ ] Auditoria centralizada
- [ ] Módulo de gestão de documentos (DMS)

### FASE 3: GTerra Expandido (6 semanas)
- [ ] Expansão de cadastro de terrenos para 21 províncias
- [ ] Motor de workflows configuráveis
- [ ] Integração PostGIS
- [ ] Melhorias no editor GIS

### FASE 4: ERP Integrado (8 semanas)
- [ ] Módulo Financeiro
- [ ] Módulo de Recursos Humanos
- [ ] Módulo de Compras e Contratos
- [ ] Módulo de Património e Inventário

### FASE 5: Integração (6 semanas)
- [ ] API Gateway
- [ ] Sistema de sincronização
- [ ] Event Bus (RabbitMQ)
- [ ] Funcionamento offline

### FASE 6: Núcleo Nacional (4 semanas)
- [ ] Dashboard nacional
- [ ] Indicadores consolidados
- [ ] Relatórios nacionais
- [ ] Administração nacional

### FASE 7: Piloto Huíla (4 semanas)
- [ ] Migração do sistema atual
- [ ] Testes de integração
- [ ] Testes de sincronização
- [ ] Homologação

### FASE 8: Expansão Nacional (contínuo)
- [ ] Ativação Node 02
- [ ] Ativação Node 03
- [ ] ...
- [ ] Ativação Node 21

### FASE 9: Mobile (8 semanas)
- [ ] Desenvolvimento app mobile
- [ ] Sincronização offline
- [ ] Testes e homologação

---

## 12. CONCLUSÃO

Esta arquitetura permite:

1. **Código único** - Um repositório, uma base de código
2. **Autonomia provincial** - Cada node opera independentemente
3. **Governança nacional** - Núcleo central com visão consolidada
4. **Escalabilidade** - Fácil adicionar novas províncias
5. **Resiliência** - Funcionamento offline com sincronização
6. **Segurança** - Isolamento de dados e auditoria completa
7. **Manutenibilidade** - Arquitetura modular e documentada

O sistema existente da Huíla serve como base funcional e será preservado durante a transformação em plataforma nacional.
