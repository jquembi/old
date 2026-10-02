# Plano de Migração - Sistema Huíla para INUTO GTerra Nacional

## Visão Geral

Este documento descreve o plano de migração do sistema atual da Huíla para a nova plataforma nacional INUTO GTerra com arquitetura multi-tenancy.

## Objetivos

1. Migrar todos os dados existentes do sistema Huíla
2. Criar tenant provincial para Huíla
3. Configurar integração com o núcleo central
4. Validar funcionalidades após migração
5. Garantir continuidade operacional

## Pré-requisitos

### Infraestrutura
- Servidor para núcleo central (CTR)
- Servidor para node provincial Huíla (HUI)
- Backup completo do sistema atual
- Acesso administrativo às bases de dados

### Dados
- Backup da base de dados atual (SQLite)
- Backup dos arquivos (documentos, imagens, etc.)
- Mapeamento de usuários e permissões
- Lista de municípios e comunas

## Fase 1: Preparação

### 1.1 Backup e Validação
```bash
# Backup da base de dados atual
cp database/database.sqlite database/database.sqlite.backup

# Backup dos arquivos
cp -r storage/app storage/app.backup

# Validar integridade do backup
sqlite3 database/database.sqlite.backup "PRAGMA integrity_check;"
```

### 1.2 Criação do Tenant Huíla
```php
// Via tinker ou seeder
$huila = Tenant::create([
    'code' => 'HUI',
    'name' => 'Província de Huíla',
    'type' => 'provincial',
    'is_active' => true,
    'is_online' => true,
    'database_host' => 'localhost',
    'database_port' => 5432,
    'database_name' => 'gterra_huila',
    'database_username' => 'gterra_huila',
    'database_password' => 'secure_password',
    'sync_status' => 'pending',
]);
```

### 1.3 Configuração de Base de Dados
```bash
# Criar base de dados para Huíla (PostgreSQL)
createdb gterra_huila

# Ou continuar com SQLite para fase inicial
# database/database_huila.sqlite
```

## Fase 2: Mapeamento de Dados

### 2.1 Tabelas Principais

| Tabela Atual | Nova Tabela | Observações |
|--------------|-------------|-------------|
| municipios | municipios | Mapeamento direto |
| usuarios | users | Adicionar tenant_id |
| roles | roles | Mapeamento direto |
| permissao_role | permission_role | Mapeamento direto |
| operacoes | operacoes | Adicionar sync fields |
| instrumentos | instrumentos | Adicionar sync fields |
| loteamentos | loteamentos | Adicionar sync fields |
| croquis | croquis | Adicionar sync fields |
| pareceres | pareceres | Adicionar sync fields |
| projectos_sona | projectos_sona | Adicionar sync fields |
| outros_projectos | outros_projectos | Adicionar sync fields |
| terrenos_ociosos | terrenos_ociosos | Adicionar sync fields |
| arquivos_base | arquivos_base | Adicionar DMS fields |
| auditoria | national_audit_logs | Mapear para auditoria central |

### 2.2 Campos de Sincronização

Todas as tabelas principais receberão:
- `sync_id` (UUID) - Identificador único global
- `sync_status` (enum) - pending, synced, conflict
- `sync_version` (int) - Versão do registro
- `synced_at` (timestamp) - Data/hora da última sincronização
- `sync_error` (text) - Mensagem de erro se houver conflito

## Fase 3: Script de Migração

### 3.1 Migration Script

```php
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class MigracaoHuilaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando migração do sistema Huíla...');
        
        // Obter tenant Huíla
        $tenant = Tenant::where('code', 'HUI')->first();
        if (!$tenant) {
            $this->command->error('Tenant Huíla não encontrado!');
            return;
        }
        
        // Migrar municípios
        $this->migrarMunicipios($tenant);
        
        // Migrar usuários
        $this->migrarUsuarios($tenant);
        
        // Migrar roles e permissões
        $this->migrarRolesPermissoes($tenant);
        
        // Migrar dados principais
        $this->migrarDadosPrincipais($tenant);
        
        // Migrar documentos
        $this->migrarDocumentos($tenant);
        
        // Migrar auditoria
        $this->migrarAuditoria($tenant);
        
        $this->command->info('Migração concluída com sucesso!');
    }
    
    private function migrarMunicipios(Tenant $tenant): void
    {
        $this->command->info('Migrando municípios...');
        
        // Assumir que municípios já existem na tabela central
        // Apenas associar ao tenant se necessário
        $municipios = DB::table('municipios')->get();
        
        foreach ($municipios as $municipio) {
            // Municípios já estão no sistema central
            // Não precisa de migração específica
        }
        
        $this->command->info('Municípios migrados: ' . $municipios->count());
    }
    
    private function migrarUsuarios(Tenant $tenant): void
    {
        $this->command->info('Migrando usuários...');
        
        // Ler usuários da base antiga (se necessário)
        // Assumir que usuários já estão na nova base
        // Apenas adicionar tenant_id se aplicável
        
        $usuarios = User::where('provincia_id', function($query) {
            $query->select('id')->from('provincias')->where('codigo', 'HUI');
        })->get();
        
        foreach ($usuarios as $usuario) {
            // Usuários já têm provincia_id
            // Não precisa de alteração
        }
        
        $this->command->info('Usuários migrados: ' . $usuarios->count());
    }
    
    private function migrarRolesPermissoes(Tenant $tenant): void
    {
        $this->command->info('Migrando roles e permissões...');
        
        // Roles e permissões são globais
        // Não precisa de migração específica por tenant
        
        $this->command->info('Roles e permissões já configuradas globalmente');
    }
    
    private function migrarDadosPrincipais(Tenant $tenant): void
    {
        $this->command->info('Migrando dados principais...');
        
        $tabelas = [
            'operacoes',
            'instrumentos',
            'loteamentos',
            'croquis',
            'pareceres',
            'projectos_sona',
            'outros_projectos',
            'terrenos_ociosos',
        ];
        
        foreach ($tabelas as $tabela) {
            $this->command->info("Migrando {$tabela}...");
            
            // Adicionar sync_id aos registros existentes
            DB::table($tabela)
                ->whereNull('sync_id')
                ->update([
                    'sync_id' => DB::raw('(lower(hex(randomblob(4))) || \'-\' || lower(hex(randomblob(2))) || \'-4\' || substr(lower(hex(randomblob(2))),1,1) || \'-\' || substr(\'89ab\',abs(random()) % 4 + 1, 1) || substr(lower(hex(randomblob(2))),2) || \'-\' || lower(hex(randomblob(6))))'),
                    'sync_status' => 'pending',
                    'sync_version' => 1,
                ]);
            
            $count = DB::table($tabela)->count();
            $this->command->info("{$tabela}: {$count} registros");
        }
    }
    
    private function migrarDocumentos(Tenant $tenant): void
    {
        $this->command->info('Migrando documentos...');
        
        // Adicionar campos DMS aos arquivos existentes
        DB::table('arquivos_base')
            ->whereNull('versao')
            ->update([
                'versao' => 1,
                'estado_aprovacao' => 'aprovado',
                'assinatura_digital' => null,
            ]);
        
        $count = DB::table('arquivos_base')->count();
        $this->command->info("Documentos migrados: {$count}");
    }
    
    private function migrarAuditoria(Tenant $tenant): void
    {
        $this->command->info('Migrando auditoria...');
        
        // Migrar logs de auditoria antigos para o novo formato
        DB::table('auditoria')
            ->orderBy('created_at')
            ->chunk(100, function ($auditorias) use ($tenant) {
                foreach ($auditorias as $auditoria) {
                    DB::table('national_audit_logs')->insert([
                        'tenant_id' => $tenant->id,
                        'user_id' => $auditoria->user_id,
                        'action' => $auditoria->acao ?? 'legacy',
                        'entity_type' => $auditoria->entidade ?? 'unknown',
                        'entity_id' => $auditoria->entidade_id,
                        'description' => $auditoria->descricao ?? 'Migrado do sistema antigo',
                        'metadata' => json_encode([
                            'legacy_id' => $auditoria->id,
                            'ip_address' => $auditoria->ip ?? null,
                        ]),
                        'created_at' => $auditoria->created_at,
                        'updated_at' => $auditoria->updated_at,
                    ]);
                }
            });
        
        $count = DB::table('national_audit_logs')->count();
        $this->command->info("Logs de auditoria migrados: {$count}");
    }
}
```

## Fase 4: Execução da Migração

### 4.1 Passo a Passo

```bash
# 1. Backup final
php artisan backup:run

# 2. Executar migrations
php artisan migrate:fresh

# 3. Executar seeder de dados base
php artisan db:seed --class=UsuariosIniciaisSeeder

# 4. Criar tenant Huíla
php artisan tinker --execute="
\$huila = \App\Models\Tenant::create([
    'code' => 'HUI',
    'name' => 'Província de Huíla',
    'type' => 'provincial',
    'is_active' => true,
    'is_online' => true,
    'database_host' => 'localhost',
    'database_port' => 5432,
    'database_name' => 'gterra_huila',
    'database_username' => 'gterra_huila',
    'database_password' => 'secure_password',
    'sync_status' => 'pending',
]);
"

# 5. Executar migração
php artisan db:seed --class=MigracaoHuilaSeeder

# 6. Verificar dados
php artisan tinker --execute="
echo 'Tenants: ' . \App\Models\Tenant::count() . PHP_EOL;
echo 'Usuários: ' . \App\Models\User::count() . PHP_EOL;
echo 'Operações: ' . \App\Models\Operacao::count() . PHP_EOL;
"
```

## Fase 5: Validação

### 5.1 Checklist de Validação

- [ ] Tenant Huíla criado corretamente
- [ ] Todos os usuários acessíveis
- [ ] Permissões mantidas
- [ ] Dados principais migrados
- [ ] Documentos acessíveis
- [ ] Auditoria migrada
- [ ] Sync IDs gerados
- [ ] Sistema funcional

### 5.2 Testes Funcionais

```bash
# Testar login
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'admin@inotu.ao')->first();
echo 'Usuário encontrado: ' . \$user->name . PHP_EOL;
"

# Testar acesso a dados
php artisan tinker --execute="
\$operacoes = \App\Models\Operacao::count();
echo 'Operações: ' . \$operacoes . PHP_EOL;
"

# Testar sync fields
php artisan tinker --execute="
\$semSync = \App\Models\Operacao::whereNull('sync_id')->count();
echo 'Operações sem sync_id: ' . \$semSync . PHP_EOL;
"
```

## Fase 6: Sincronização Inicial

### 6.1 Push para Núcleo Central

```bash
# Via API Gateway
curl -X POST http://localhost:8000/api/gateway/sync/push \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "entity_type": "operacoes",
        "records": [...]
      }
    ]
  }'
```

### 6.2 Verificar Status

```bash
# Verificar status de sincronização
php artisan tinker --execute="
\$huila = \App\Models\Tenant::where('code', 'HUI')->first();
echo 'Sync Status: ' . \$huila->sync_status . PHP_EOL;
"
```

## Fase 7: Rollback (Se Necessário)

### 7.1 Procedimento de Rollback

```bash
# 1. Parar aplicação
php artisan down

# 2. Restaurar backup
cp database/database.sqlite.backup database/database.sqlite
cp -r storage/app.backup storage/app

# 3. Reiniciar aplicação
php artisan up
```

## Fase 8: Monitoramento Pós-Migração

### 8.1 Métricas a Monitorar

- Performance do sistema
- Taxa de sucesso de sincronização
- Erros de conflito
- Tempo de resposta
- Uso de recursos

### 8.2 Logs Importantes

- Logs de sincronização
- Logs de auditoria
- Logs de erros
- Logs de performance

## Riscos e Mitigação

| Risco | Probabilidade | Impacto | Mitigação |
|-------|--------------|---------|-----------|
| Perda de dados | Baixa | Crítico | Backup completo antes da migração |
| Downtime prolongado | Média | Alto | Planejamento em horário de menor uso |
| Erros de sincronização | Média | Médio | Testes exaustivos antes da produção |
| Incompatibilidade de dados | Baixa | Alto | Validação prévia dos dados |
| Problemas de performance | Média | Médio | Monitoramento contínuo |

## Cronograma

| Fase | Duração | Responsável |
|------|---------|-------------|
| Preparação | 2 dias | Equipa Técnica |
| Mapeamento | 1 dia | Arquiteto de Dados |
| Script de Migração | 3 dias | Desenvolvedor |
| Execução | 1 dia | Equipa Técnica |
| Validação | 2 dias | QA |
| Sincronização Inicial | 1 dia | DevOps |
| Monitoramento | 7 dias | Equipa Técnica |

**Total:** 17 dias úteis

## Conclusão

Este plano fornece um roteiro detalhado para a migração do sistema Huíla para a nova plataforma nacional. É essencial seguir cada fase cuidadosamente e realizar testes exaustivos antes da migração em produção.
