# PLANO DE MIGRAÇÕES DE BASE DE DADOS
## INUTO GTerra - Plataforma Nacional

**Versão:** 1.0  
**Data:** 28 Agosto 2026

---

## 1. ESTRATÉGIA DE MIGRAÇÃO

### 1.1 Abordagem

A migração será realizada em três fases:

1. **Fase 1 - Preparação:** Criar estrutura central e preparar tabelas existentes
2. **Fase 2 - Multi-tenancy:** Implementar isolamento por tenant
3. **Fase 3 - PostGIS:** Migrar para PostgreSQL + PostGIS (opcional, por node)

### 1.2 Ordem de Execução

As migrations devem ser executadas na seguinte ordem:

1. Tabelas centrais (Núcleo Nacional)
2. Campos de sincronização em tabelas existentes
3. Novas tabelas de workflows
4. Tabelas ERP
5. Índices e optimizações

---

## 2. MIGRAÇÕES DO NÚCLEO CENTRAL

### 2.1 Tabela Tenants

```php
// database/migrations/2026_08_28_000001_create_tenants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // HUI, BGO, LUA...
            $table->string('name');
            $table->enum('type', ['central', 'provincial'])->default('provincial');
            $table->string('database_name');
            $table->string('database_host')->nullable();
            $table->integer('database_port')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('sync_status', ['online', 'offline', 'syncing'])->default('online');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
            $table->index('sync_status');
        });

        // Criar tenant central
        DB::table('tenants')->insert([
            'code' => 'CTR',
            'name' => 'Núcleo Central Nacional',
            'type' => 'central',
            'database_name' => env('DB_DATABASE', 'gterra_central'),
            'is_active' => true,
            'sync_status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

### 2.2 Tabela National Indicators

```php
// database/migrations/2026_08_28_000002_create_national_indicators_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('indicator_type', 100); // processos, terras, receitas, etc.
            $table->json('value'); // Dados do indicador
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'indicator_type']);
            $table->index('period_start');
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_indicators');
    }
};
```

### 2.3 Tabela Sync Logs

```php
// database/migrations/2026_08_28_000003_create_sync_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('target_tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->string('sync_type', 50); // push_to_central, pull_from_central, peer_to_peer
            $table->enum('status', ['pending', 'success', 'failed', 'partial'])->default('pending');
            $table->integer('records_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['source_tenant_id', 'status']);
            $table->index('sync_type');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
```

### 2.4 Tabela National Parameters

```php
// database/migrations/2026_08_28_000004_create_national_parameters_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_key', 255)->unique();
            $table->text('parameter_value');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('parameter_key');
            $table->index('is_active');
        });

        // Parâmetros iniciais
        DB::table('national_parameters')->insert([
            [
                'parameter_key' => 'sync.interval_minutes',
                'parameter_value' => '30',
                'description' => 'Intervalo de sincronização automática em minutos',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parameter_key' => 'sync.retry_attempts',
                'parameter_value' => '3',
                'description' => 'Número de tentativas de retry em caso de falha',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parameter_key' => 'nodes.max_offline_hours',
                'parameter_value' => '24',
                'description' => 'Máximo de horas offline antes de alerta',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('national_parameters');
    }
};
```

---

## 3. MIGRAÇÕES DE SINCRONIZAÇÃO

### 3.1 Campos de Sincronização - Operações

```php
// database/migrations/2026_08_28_000005_add_sync_fields_to_operacoes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operacoes', function (Blueprint $table) {
            $table->uuid('sync_id')->unique()->after('id');
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced')->after('sync_id');
            $table->integer('sync_version')->default(1)->after('sync_status');
            $table->timestamp('synced_at')->nullable()->after('sync_version');
            $table->text('sync_error')->nullable()->after('synced_at');
            
            $table->index('sync_id');
            $table->index('sync_status');
        });

        // Gerar sync_id para registos existentes
        DB::table('operacoes')->whereNull('sync_id')->get()->each(function ($operacao) {
            DB::table('operacoes')
                ->where('id', $operacao->id)
                ->update(['sync_id' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('operacoes', function (Blueprint $table) {
            $table->dropColumn(['sync_id', 'sync_status', 'sync_version', 'synced_at', 'sync_error']);
        });
    }
};
```

### 3.2 Campos de Sincronização - Outras Tabelas Principais

```php
// database/migrations/2026_08_28_000006_add_sync_fields_to_main_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'instrumentos',
            'loteamentos',
            'croquis',
            'terrenos_ociosos',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->uuid('sync_id')->unique()->after('id');
                    $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced')->after('sync_id');
                    $table->integer('sync_version')->default(1)->after('sync_status');
                    $table->timestamp('synced_at')->nullable()->after('sync_version');
                    $table->text('sync_error')->nullable()->after('synced_at');
                    
                    $table->index('sync_id');
                    $table->index('sync_status');
                });

                // Gerar sync_id para registos existentes
                DB::table($table)->whereNull('sync_id')->get()->each(function ($record) use ($table) {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['sync_id' => (string) Str::uuid()]);
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'instrumentos',
            'loteamentos',
            'croquis',
            'terrenos_ociosos',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['sync_id', 'sync_status', 'sync_version', 'synced_at', 'sync_error']);
                });
            }
        }
    }
};
```

### 3.3 Tabela Offline Operations

```php
// database/migrations/2026_08_28_000007_create_offline_operations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('operation_type', 50); // create, update, delete
            $table->string('entity_type', 100); // operacao, instrumento, etc.
            $table->json('payload'); // Dados da operação
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_operations');
    }
};
```

---

## 4. MIGRAÇÕES DE WORKFLOWS

### 4.1 Tabela Workflows

```php
// database/migrations/2026_08_28_000008_create_workflows_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tipo_processo', 100); // processo_fundiario, licenciamento, etc.
            $table->text('description')->nullable();
            $table->json('configuracao'); // Definição de estados e transições
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('tipo_processo');
            $table->index('activo');
        });

        // Workflow padrão para processos fundiários
        DB::table('workflows')->insert([
            'name' => 'Processo Fundiário Padrão',
            'tipo_processo' => 'processo_fundiario',
            'description' => 'Workflow padrão para processos de concessão de terras',
            'configuracao' => $this->getDefaultWorkflowConfig(),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getDefaultWorkflowConfig(): array
    {
        return [
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
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
```

### 4.2 Tabela Processo Workflows

```php
// database/migrations/2026_08_28_000009_create_processo_workflows_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('restrict');
            $table->morphs('processo'); // processo_id, processo_type (Operacao, TerrenoOcioso, etc.)
            $table->string('estado_actual');
            $table->json('dados')->nullable(); // Dados específicos do processo
            $table->foreignId('iniciado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            
            $table->index(['processo_id', 'processo_type']);
            $table->index('estado_actual');
            $table->index('iniciado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_workflows');
    }
};
```

### 4.3 Tabela Workflow Transições

```php
// database/migrations/2026_08_28_000010_create_workflow_transicoes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_transicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_workflow_id')->constrained('processo_workflows')->onDelete('cascade');
            $table->string('de_estado');
            $table->string('para_estado');
            $table->foreignId('realizado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->json('dados')->nullable(); // Dados da transição
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('processo_workflow_id');
            $table->index(['de_estado', 'para_estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_transicoes');
    }
};
```

---

## 5. MIGRAÇÕES ERP

### 5.1 Módulo Financeiro

```php
// database/migrations/2026_08_28_000011_create_erp_plano_contas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_plano_contas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('designacao');
            $table->enum('tipo', ['receita', 'despesa', 'ativo', 'passivo', 'patrimonio_liquido']);
            $table->integer('nivel')->default(1); // 1-5 para hierarquia
            $table->foreignId('conta_pai_id')->nullable()->constrained('erp_plano_contas')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('tipo');
            $table->index('conta_pai_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_plano_contas');
    }
};
```

```php
// database/migrations/2026_08_28_000012_create_erp_contas_pagar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('fornecedor_id')->nullable()->constrained('erp_fornecedores')->onDelete('set null');
            $table->decimal('valor', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'paga', 'parcialmente_paga', 'cancelada'])->default('pendente');
            $table->foreignId('categoria_id')->nullable()->constrained('erp_plano_contas')->onDelete('set null');
            $table->text('descricao')->nullable();
            $table->foreignId('anexo_id')->nullable()->constrained('arquivos_base')->onDelete('set null');
            $table->uuid('sync_id')->unique();
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced');
            $table->timestamps();
            
            $table->index('numero');
            $table->index('status');
            $table->index('data_vencimento');
            $table->index('sync_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_contas_pagar');
    }
};
```

### 5.2 Módulo RH

```php
// database/migrations/2026_08_28_000013_create_erp_funcionarios_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('numero_funcionario')->unique();
            $table->string('nome');
            $table->string('email')->unique()->nullable();
            $table->string('telefone')->nullable();
            $table->foreignId('departamento_id')->nullable()->constrained('erp_departamentos')->onDelete('set null');
            $table->foreignId('cargo_id')->nullable()->constrained('erp_cargos')->onDelete('set null');
            $table->date('data_admissao');
            $table->date('data_demissao')->nullable();
            $table->enum('status', ['activo', 'inactivo', 'suspenso'])->default('activo');
            $table->decimal('salario_base', 15, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->uuid('sync_id')->unique();
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced');
            $table->timestamps();
            
            $table->index('numero_funcionario');
            $table->index('departamento_id');
            $table->index('cargo_id');
            $table->index('status');
            $table->index('sync_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_funcionarios');
    }
};
```

### 5.3 Módulo Compras

```php
// database/migrations/2026_08_28_000014_create_erp_fornecedores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero_fornecedor')->unique();
            $table->string('nome');
            $table->string('nif')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->text('endereco')->nullable();
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->text('observacoes')->nullable();
            $table->uuid('sync_id')->unique();
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced');
            $table->timestamps();
            
            $table->index('numero_fornecedor');
            $table->index('nif');
            $table->index('status');
            $table->index('sync_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_fornecedores');
    }
};
```

### 5.4 Módulo Património

```php
// database/migrations/2026_08_28_000015_create_erp_bens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_bens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_patrimonio')->unique();
            $table->string('designacao');
            $table->foreignId('categoria_id')->nullable()->constrained('erp_categorias_bens')->onDelete('set null');
            $table->enum('tipo', ['equipamento', 'viatura', 'imovel', 'mobiliario', 'outro']);
            $table->decimal('valor_aquisicao', 15, 2)->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->string('localizacao')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('erp_funcionarios')->onDelete('set null');
            $table->enum('estado', ['novo', 'bom', 'regular', 'mau', 'inutilizado'])->default('bom');
            $table->text('observacoes')->nullable();
            $table->uuid('sync_id')->unique();
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced');
            $table->timestamps();
            
            $table->index('numero_patrimonio');
            $table->index('tipo');
            $table->index('categoria_id');
            $table->index('estado');
            $table->index('sync_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_bens');
    }
};
```

---

## 6. MIGRAÇÃO PARA POSTGIS (Opcional por Node)

### 6.1 Preparação para PostGIS

```php
// database/migrations/2026_08_28_000016_prepare_postgis_migration.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migration é apenas para documentação
        // A migração real para PostGIS será feita por node individualmente
        // quando o node estiver pronto para migrar de SQLite para PostgreSQL
        
        // Passos manuais por node:
        // 1. Exportar dados do SQLite
        // 2. Criar base de dados PostgreSQL com extensão PostGIS
        // 3. Importar dados
        // 4. Converter colunas geometry para tipo PostGIS
        // 5. Criar índices espaciais
    }

    public function down(): void
    {
        // Rollback manual por node se necessário
    }
};
```

### 6.2 Exemplo de Comandos PostGIS

```sql
-- Ativar extensão PostGIS
CREATE EXTENSION IF NOT EXISTS postgis;

-- Converter coluna geometry para tipo PostGIS
ALTER TABLE map_features 
ALTER COLUMN geometry TYPE geometry(Geometry,4326) 
USING ST_GeomFromText(geometry);

-- Criar índice espacial
CREATE INDEX map_features_geometry_idx 
ON map_features 
USING GIST (geometry);

-- Adicionar colunas calculadas
ALTER TABLE map_features 
ADD COLUMN area_m2 DECIMAL(15,2);

UPDATE map_features 
SET area_m2 = ST_Area(geometry::geography);

ALTER TABLE map_features 
ADD COLUMN perimetro_m DECIMAL(15,2);

UPDATE map_features 
SET perimetro_m = ST_Perimeter(geometry::geography);

ALTER TABLE map_features 
ADD COLUMN centroid POINT;

UPDATE map_features 
SET centroid = ST_Centroid(geometry);
```

---

## 7. ORDEM DE EXECUÇÃO

### 7.1 Sequência Recomendada

```bash
# FASE 1: Núcleo Central
php artisan migrate --path=database/migrations/2026_08_28_000001_create_tenants_table.php
php artisan migrate --path=database/migrations/2026_08_28_000002_create_national_indicators_table.php
php artisan migrate --path=database/migrations/2026_08_28_000003_create_sync_logs_table.php
php artisan migrate --path=database/migrations/2026_08_28_000004_create_national_parameters_table.php

# FASE 2: Sincronização
php artisan migrate --path=database/migrations/2026_08_28_000005_add_sync_fields_to_operacoes_table.php
php artisan migrate --path=database/migrations/2026_08_28_000006_add_sync_fields_to_main_tables.php
php artisan migrate --path=database/migrations/2026_08_28_000007_create_offline_operations_table.php

# FASE 3: Workflows
php artisan migrate --path=database/migrations/2026_08_28_000008_create_workflows_table.php
php artisan migrate --path=database/migrations/2026_08_28_000009_create_processo_workflows_table.php
php artisan migrate --path=database/migrations/2026_08_28_000010_create_workflow_transicoes_table.php

# FASE 4: ERP (opcional, por módulo)
php artisan migrate --path=database/migrations/2026_08_28_000011_create_erp_plano_contas_table.php
php artisan migrate --path=database/migrations/2026_08_28_000012_create_erp_contas_pagar_table.php
php artisan migrate --path=database/migrations/2026_08_28_000013_create_erp_funcionarios_table.php
php artisan migrate --path=database/migrations/2026_08_28_000014_create_erp_fornecedores_table.php
php artisan migrate --path=database/migrations/2026_08_28_000015_create_erp_bens_table.php
```

### 7.2 Rollback

```bash
# Rollback completo
php artisan migrate:rollback

# Rollback por steps
php artisan migrate:rollback --step=5
```

---

## 8. CONSIDERAÇÕES IMPORTANTES

### 8.1 Dados Existentes

- Todas as migrations que adicionam campos de sincronização geram `sync_id` para registos existentes
- Índices são criados para optimizar performance
- Valores default são definidos para manter compatibilidade

### 8.2 Performance

- Índices são criados em colunas frequentemente usadas em queries
- Campos JSON são usados para flexibilidade em configurações
- Timestamps são usados para rastreabilidade

### 8.3 Segurança

- Foreign keys garantem integridade referencial
- Soft deletes podem ser implementados se necessário
- Campos de auditoria (created_by, updated_by) são incluídos

### 8.4 Escalabilidade

- Estrutura preparada para multi-tenancy
- Campos de sincronização permitem operação offline
- Índices optimizam queries em grandes volumes de dados

---

## 9. TESTES DE MIGRAÇÃO

### 9.1 Testes Recomendados

```php
// tests/Database/MigrationsTest.php

use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenants_table_exists()
    {
        $this->assertDatabaseHas('tenants', [
            'code' => 'CTR',
            'type' => 'central',
        ]);
    }

    public function test_sync_fields_added_to_operacoes()
    {
        $operacao = \App\Models\Operacao::first();
        $this->assertNotNull($operacao->sync_id);
        $this->assertEquals('synced', $operacao->sync_status);
    }

    public function test_workflow_table_exists()
    {
        $this->assertDatabaseHas('workflows', [
            'tipo_processo' => 'processo_fundiario',
        ]);
    }
}
```

---

## 10. CONCLUSÃO

Este plano de migrations fornece:

1. **Estrutura central** para governança nacional
2. **Campos de sincronização** para operação multi-node
3. **Sistema de workflows** configurável
4. **Módulos ERP** integrados
5. **Preparação para PostGIS** quando necessário

As migrations podem ser executadas incrementalmente, permitindo uma transição suave do sistema actual para a plataforma nacional.
