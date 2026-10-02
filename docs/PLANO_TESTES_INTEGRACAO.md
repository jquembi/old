# Plano de Testes de Integração - INUTO GTerra

## Visão Geral

Este documento descreve o plano de testes de integração para validar a migração do sistema Huíla e a funcionalidade da plataforma nacional INUTO GTerra.

## Objetivos

1. Validar integridade dos dados migrados
2. Testar funcionalidades principais do sistema
3. Validar sincronização entre nodes
4. Testar funcionamento offline
5. Validar workflows configuráveis
6. Testar auditoria centralizada
7. Validar permissões e segurança

## Estrutura de Testes

### 1. Testes de Migração de Dados

#### 1.1 Validação de Integridade
```php
// Teste: Verificar contagem de registros
php artisan tinker --execute="
echo 'Municípios: ' . \App\Models\Municipio::count() . PHP_EOL;
echo 'Usuários: ' . \App\Models\User::count() . PHP_EOL;
echo 'Operações: ' . \App\Models\Operacao::count() . PHP_EOL;
echo 'Instrumentos: ' . \App\Models\Instrumento::count() . PHP_EOL;
echo 'Terrenos Ociosos: ' . \App\Models\TerrenoOcioso::count() . PHP_EOL;
"

// Teste: Verificar sync IDs
php artisan tinker --execute="
\$semSync = \App\Models\Operacao::whereNull('sync_id')->count();
echo 'Operações sem sync_id: ' . \$semSync . PHP_EOL;
\$comSync = \App\Models\Operacao::whereNotNull('sync_id')->count();
echo 'Operações com sync_id: ' . \$comSync . PHP_EOL;
"

// Teste: Verificar documentos
php artisan tinker --execute="
\$docs = \App\Models\ArquivoBase::count();
echo 'Documentos: ' . \$docs . PHP_EOL;
\$semVersao = \App\Models\ArquivoBase::whereNull('versao')->count();
echo 'Documentos sem versão: ' . \$semVersao . PHP_EOL;
"
```

#### 1.2 Validação de Relações
```php
// Teste: Verificar relações de usuários
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'admin@inotu.ao')->first();
if (\$user) {
    echo 'Usuário: ' . \$user->name . PHP_EOL;
    echo 'Província: ' . (\$user->provincia->nome ?? 'N/A') . PHP_EOL;
    echo 'Role: ' . (\$user->roles->first()->nome ?? 'N/A') . PHP_EOL;
}
"

// Teste: Verificar relações de operações
php artisan tinker --execute="
\$op = \App\Models\Operacao::first();
if (\$op) {
    echo 'Operação: ' . \$op->nome . PHP_EOL;
    echo 'Município: ' . (\$op->municipio->nome ?? 'N/A') . PHP_EOL;
    echo 'Sync ID: ' . (\$op->sync_id ?? 'N/A') . PHP_EOL;
}
"
```

### 2. Testes de Funcionalidades Principais

#### 2.1 Testes de Autenticação
```bash
# Teste 1: Login com usuário válido
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@inotu.ao",
    "password": "password1"
  }'

# Teste 2: Login com usuário inválido
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "invalid@test.com",
    "password": "wrong"
  }'

# Teste 3: Acesso protegido
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}"
```

#### 2.2 Testes de Permissões
```php
// Teste: Verificar permissões de usuário
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'admin@inotu.ao')->first();
echo 'Tem permissão operacoes.index: ' . (\$user->temPermissao('operacoes.index') ? 'Sim' : 'Não') . PHP_EOL;
echo 'Tem permissao processos.create: ' . (\$user->temPermissao('processos.create') ? 'Sim' : 'Não') . PHP_EOL;
"

// Teste: Verificar escopo de usuário
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'admin@inotu.ao')->first();
echo 'É INOTU: ' . (\$user->isInotu() ? 'Sim' : 'Não') . PHP_EOL;
echo 'É Provincial: ' . (\$user->isProvincial() ? 'Sim' : 'Não') . PHP_EOL;
"
```

#### 2.3 Testes de CRUD
```bash
# Teste: Criar operação
curl -X POST http://localhost:8000/api/operacoes \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Operação Teste",
    "municipio_id": 1,
    "descricao": "Teste de integração"
  }'

# Teste: Listar operações
curl -X GET http://localhost:8000/api/operacoes \
  -H "Authorization: Bearer {token}"

# Teste: Atualizar operação
curl -X PUT http://localhost:8000/api/operacoes/{id} \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Operação Teste Atualizada"
  }'

# Teste: Eliminar operação
curl -X DELETE http://localhost:8000/api/operacoes/{id} \
  -H "Authorization: Bearer {token}"
```

### 3. Testes de Sincronização

#### 3.1 Testes de Push para Central
```bash
# Teste: Push de dados
curl -X POST http://localhost:8000/api/gateway/sync/push \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "entity_type": "operacoes",
        "records": [
          {
            "sync_id": "uuid",
            "data": {...}
          }
        ]
      }
    ]
  }'

# Teste: Verificar status de sincronização
curl -X GET http://localhost:8000/api/gateway/sync/status/{sync_log_id} \
  -H "Authorization: Bearer {token}"

# Teste: Histórico de sincronização
curl -X GET http://localhost:8000/api/gateway/sync/history \
  -H "Authorization: Bearer {token}"
```

#### 3.2 Testes de Pull do Central
```bash
# Teste: Pull de dados
curl -X POST http://localhost:8000/api/gateway/sync/pull \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "entity_type": "national_indicators",
        "records": [...]
      }
    ]
  }'
```

#### 3.3 Testes de Conflitos
```php
// Teste: Simular conflito de versão
php artisan tinker --execute="
\$op = \App\Models\Operacao::first();
\$op->update([
    'sync_version' => 2,
    'sync_status' => 'conflict',
    'sync_error' => 'Conflito de versão detectado'
]);
echo 'Conflito simulado para operação: ' . \$op->nome . PHP_EOL;
"

// Teste: Resolver conflito
php artisan tinker --execute="
\$op = \App\Models\Operacao::where('sync_status', 'conflict')->first();
if (\$op) {
    \$op->update([
        'sync_status' => 'synced',
        'sync_error' => null
    ]);
    echo 'Conflito resolvido para operação: ' . \$op->nome . PHP_EOL;
}
"
```

### 4. Testes de Funcionamento Offline

#### 4.1 Testes de Queue Offline
```bash
# Teste: Enfileirar operação offline
curl -X POST http://localhost:8000/api/gateway/offline/queue \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "operation_type": "create",
    "entity_type": "operacoes",
    "payload": {
      "nome": "Operação Offline",
      "municipio_id": 1
    },
    "priority": "normal"
  }'

# Teste: Listar operações pendentes
curl -X GET http://localhost:8000/api/gateway/offline/pending \
  -H "Authorization: Bearer {token}"

# Teste: Processar operações offline
curl -X POST http://localhost:8000/api/gateway/offline/process \
  -H "Authorization: Bearer {token}"
```

#### 4.2 Testes de Retry
```php
// Teste: Simular falha de operação offline
php artisan tinker --execute="
\$op = \App\Models\OfflineOperation::first();
if (\$op) {
    \$op->markAsFailed('Erro de teste');
    \$op->increment('retry_count');
    echo 'Operação marcada como falha: ' . \$op->id . PHP_EOL;
}
"

// Teste: Retentar operação
php artisan tinker --execute="
\$op = \App\Models\OfflineOperation::where('status', 'failed')->first();
if (\$op) {
    \$op->markAsProcessing();
    echo 'Operação marcada para retry: ' . \$op->id . PHP_EOL;
}
"
```

### 5. Testes de Workflows

#### 5.1 Testes de Transição de Workflow
```php
// Teste: Criar workflow para processo
php artisan tinker --execute="
\$workflow = \App\Models\Workflow::where('tipo_processo', 'processo_fundiario')->first();
if (\$workflow) {
    echo 'Workflow encontrado: ' . \$workflow->name . PHP_EOL;
    echo 'Estados: ' . json_encode(\$workflow->getEstados()) . PHP_EOL;
}
"

// Teste: Criar instância de workflow
php artisan tinker --execute="
\$workflow = \App\Models\Workflow::where('tipo_processo', 'processo_fundiario')->first();
\$processo = \App\Models\Operacao::first();

\$pw = \App\Models\ProcessoWorkflow::create([
    'workflow_id' => \$workflow->id,
    'processo_id' => \$processo->id,
    'processo_type' => \App\Models\Operacao::class,
    'estado_actual' => 'rececao',
    'dados' => [],
    'iniciado_por' => 1,
    'iniciado_em' => now(),
]);

echo 'Workflow criado para operação: ' . \$processo->nome . PHP_EOL;
echo 'Estado atual: ' . \$pw->estado_actual . PHP_EOL;
"

// Teste: Transitar workflow
php artisan tinker --execute="
\$pw = \App\Models\ProcessoWorkflow::first();
\$user = \App\Models\User::first();

if (\$pw && \$user) {
    \$pw->transitar('validacao', \$user, ['observacao' => 'Teste']);
    echo 'Workflow transitado para: ' . \$pw->estado_actual . PHP_EOL;
}
"
```

### 6. Testes de Auditoria

#### 6.1 Testes de Log de Ações
```php
// Teste: Verificar logs de auditoria
php artisan tinker --execute="
\$logs = \App\Models\NationalAuditLog::latest()->limit(10)->get();
foreach (\$logs as \$log) {
    echo \$log->action . ' - ' . \$log->description . PHP_EOL;
}
"

// Teste: Log de ação manual
php artisan tinker --execute="
\$service = app(\App\Services\AuditoriaCentralizadaService::class);
\$service->logAction(
    'test_action',
    'TestEntity',
    1,
    'Teste de auditoria',
    ['test' => true]
);
echo 'Ação de teste logada' . PHP_EOL;
"
```

### 7. Testes de Performance

#### 7.1 Testes de Carga
```bash
# Teste: 100 requisições simultâneas
ab -n 100 -c 10 -H "Authorization: Bearer {token}" \
   http://localhost:8000/api/operacoes

# Teste: Tempo de resposta
curl -w "@curl-format.txt" -o /dev/null -s \
  http://localhost:8000/api/operacoes

# curl-format.txt:
# time_total: %{time_total}\n
# time_connect: %{time_connect}\n
# time_starttransfer: %{time_starttransfer}\n
```

#### 7.2 Testes de Banco de Dados
```php
// Teste: Tempo de query
php artisan tinker --execute="
\$start = microtime(true);
\$ops = \App\Models\Operacao::with(['municipio', 'user'])->get();
\$end = microtime(true);
echo 'Tempo: ' . (\$end - \$start) . 's' . PHP_EOL;
echo 'Registros: ' . \$ops->count() . PHP_EOL;
"
```

### 8. Testes de Segurança

#### 8.1 Testes de Autenticação
```bash
# Teste: Tentativa de acesso sem token
curl -X GET http://localhost:8000/api/operacoes

# Teste: Token inválido
curl -X GET http://localhost:8000/api/operacoes \
  -H "Authorization: Bearer invalid_token"

# Teste: Token expirado
curl -X GET http://localhost:8000/api/operacoes \
  -H "Authorization: Bearer expired_token"
```

#### 8.2 Testes de Permissões
```bash
# Teste: Acesso sem permissão
curl -X POST http://localhost:8000/api/operacoes \
  -H "Authorization: Bearer {user_without_permission_token}" \
  -H "Content-Type: application/json" \
  -d '{"nome": "Teste"}'

# Teste: Acesso a recurso de outro tenant
curl -X GET http://localhost:8000/api/operacoes/{id} \
  -H "Authorization: Bearer {other_tenant_token}"
```

## Checklist de Validação

### Pré-Migração
- [ ] Backup completo realizado
- [ ] Tenant Huíla criado
- [ ] Configuração de base de dados validada
- [ ] Usuários de teste criados

### Pós-Migração
- [ ] Todos os dados migrados
- [ ] Sync IDs gerados
- [ ] Relações preservadas
- [ ] Documentos acessíveis
- [ ] Auditoria migrada

### Funcionalidades
- [ ] Login funcional
- [ ] Permissões corretas
- [ ] CRUD operacional
- [ ] Workflows funcionais
- [ ] Sincronização ativa
- [ ] Offline queue funcional

### Performance
- [ ] Tempo de resposta < 2s
- [ ] Queries otimizadas
- [ ] Sem memory leaks
- [ ] Uso de CPU normal

### Segurança
- [ ] Autenticação segura
- [ ] Permissões respeitadas
- [ ] Isolamento de tenants
- [ ] Logs de auditoria ativos

## Relatório de Testes

### Template de Relatório

```markdown
# Relatório de Testes de Integração

**Data:** {data}
**Executor:** {executor}
**Ambiente:** {ambiente}

## Resumo
- **Testes Executados:** {total}
- **Testes Passados:** {passados}
- **Testes Falhados:** {falhados}
- **Taxa de Sucesso:** {taxa}%

## Detalhes por Categoria

### Migração de Dados
- [ ] Validação de integridade: {status}
- [ ] Validação de relações: {status}

### Funcionalidades
- [ ] Autenticação: {status}
- [ ] Permissões: {status}
- [ ] CRUD: {status}

### Sincronização
- [ ] Push para central: {status}
- [ ] Pull do central: {status}
- [ ] Resolução de conflitos: {status}

### Offline
- [ ] Queue offline: {status}
- [ ] Processamento: {status}
- [ ] Retry: {status}

### Workflows
- [ ] Criação de workflow: {status}
- [ ] Transição de estados: {status}

### Auditoria
- [ ] Log de ações: {status}
- [ ] Histórico: {status}

### Performance
- [ ] Tempo de resposta: {status}
- [ ] Carga: {status}

### Segurança
- [ ] Autenticação: {status}
- [ ] Permissões: {status}

## Problemas Encontrados
1. {problema 1}
2. {problema 2}

## Recomendações
1. {recomendação 1}
2. {recomendação 2}
```

## Cronograma de Testes

| Fase | Duração | Responsável |
|------|---------|-------------|
| Preparação | 1 dia | QA |
| Testes de Migração | 2 dias | QA |
| Testes Funcionais | 3 dias | QA |
| Testes de Sincronização | 2 dias | DevOps |
| Testes de Performance | 2 dias | QA |
| Testes de Segurança | 2 dias | Security |
| Validação Final | 1 dia | Equipa Técnica |

**Total:** 13 dias úteis

## Conclusão

Este plano fornece uma abordagem abrangente para testar a integração do sistema Huíla na plataforma nacional INUTO GTerra. É essencial executar todos os testes antes da migração em produção para garantir a estabilidade e funcionalidade do sistema.
