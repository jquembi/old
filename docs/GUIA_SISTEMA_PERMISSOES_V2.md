# Guia de Uso: Novo Sistema de Permissões V2

## 📚 Índice
1. [Visão Geral](#visão-geral)
2. [Conceitos Principais](#conceitos-principais)
3. [Como Usar](#como-usar)
4. [Exemplos Práticos](#exemplos-práticos)
5. [Migrração de V1 para V2](#migração-de-v1-para-v2)
6. [FAQ](#faq)

---

## 🎯 Visão Geral

O novo sistema de permissões V2 traz:
- ✅ **Contexto**: Permissões que entendem "quem", "onde" e "o quê"
- ✅ **Cache**: Performance melhor com cache automático
- ✅ **Granularidade**: Controle fino sobre quem pode fazer o quê
- ✅ **Auditoria**: Registro completo de todos os acessos
- ✅ **Compatibilidade**: 100% compatível com V1

---

## 🧠 Conceitos Principais

### 1. Permissão Básica
```php
// V1 (ainda funciona)
$user->temPermissao('instrumentos.editar')

// V2 (novo, recomendado)
$user->podeAcesso('instrumentos.editar')
```

### 2. Permissão com Contexto
```php
// Agregar contexto geográfico
$contexto = new PermissionContext(
    municipioId: 5,
    provinciaId: 2,
);

$user->podeAcesso('instrumentos.editar', $contexto)
```

### 3. Permissão de Recurso
```php
// Verificar directo num recurso
$instrumento = Instrumento::find(1);
$user->podeEditarRecurso($instrumento)
```

### 4. Permissão Dinâmica
```php
// Tenta várias permutações automaticamente
$user->podeFazer('editar', 'rascunho')
// Verifica em ordem:
// - editar_rascunho
// - editar_municipio
// - editar_proprio
// - editar
```

---

## 💻 Como Usar

### Verificar Permissões

#### Interface Principal
```php
// Verificação simples
if ($user->podeAcesso('instrumentos.criar')) {
    // Criar instrumento
}

// Com contexto
$contexto = PermissionContext::fromUserAndResource($user, $municipio);
if ($user->podeAcesso('municipios.gerir_config', $contexto)) {
    // Gerir config
}

// Várias alternativas
if ($user->temAlgumaPermissao([
    'instrumentos.editar_municipio',
    'instrumentos.editar_admin',
])) {
    // Tem pelo menos uma
}

// Todas as permissões
if ($user->temTodasAsPermissoes([
    'pareceres.criar',
    'pareceres.submeter',
])) {
    // Tem todas
}
```

#### Interface Genérica para Recursos
```php
$instrumento = Instrumento::find(1);

$user->podeVerRecurso($instrumento)      // .ver
$user->podeEditarRecurso($instrumento)   // .editar
$user->podeEliminarRecurso($instrumento) // .eliminar
```

#### Permissão Dinâmica
```php
// Tenta estado específico primeiro
$user->podeFazer('editar', 'rascunho')
// Equivalente a plural:
// - editar_rascunho
// - editar_municipio
// - editar_proprio
// - editar

// Com contexto
$ctx = PermissionContext::fromUserAndResource($user, $operacao);
$user->podeFazer('revisar', null, $ctx)
```

### Obter Permissões

```php
// Todas as permissões efetivas (com cache)
$permissoes = $user->permissoesEfetivas();

// Nomes das permissões
$nomes = $user->nomesPermissoes();

// Compatível V1
$todas = $user->todasPermissoes();

// Verificar permissões específicas
if ($nomes->contains('instrumentos.editar_admin')) {
    // Pode editar qualquer instrumento (admin)
}
```

### Cache de Permissões

```php
// Cache é automático
// Invalidar quando mudar roles/permissões
$user->invalidarCachePermissoes();

// Depois desta chamada, próxima verificação recarrega cache
$user->podeAcesso('instrumentos.ver');
```

### Auditoria

```php
// Obter estatísticas de acesso
$stats = $user->estatisticasAcesso();
// [
//     'total_acessos' => 1250,
//     'acessos_permitidos' => 1200,
//     'acessos_negados' => 50,
//     'permissoes_unicas' => 45,
//     'ultimo_acesso' => Carbon instance
// ]

// Desde data específica
$stats = $user->estatisticasAcesso(\Carbon\Carbon::now()->subDays(7));

// Consultar tabela de auditoria
$auditorias = PermissionAudit::deUtilizador($user->id)
    ->negado()
    ->from(\Carbon\Carbon::now()->subDays(1))
    ->get();
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Proteger Criação em Livewire

**Antes (V1)**:
```php
public function guardar(): void
{
    if (!$this->user->podeEditarDados()) {
        flash('Sem permissão');
        return;
    }
    // ...
}
```

**Agora (V2)**:
```php
public function guardar(): void
{
    if (!$this->user->podeAcesso('instrumentos.criar')) {
        flash('Sem permissão para criar');
        return;
    }
    // ...
}
```

### Exemplo 2: Editar com Contexto

**Antes (V1)**:
```php
public function editar(int $id): void
{
    $instrumento = Instrumento::findOrFail($id);
    if (!$this->user->podeEditarMunicipio($instrumento->municipio_id)) {
        flash('Sem permissão');
        return;
    }
    // ...
}
```

**Agora (V2)**:
```php
public function editar(int $id): void
{
    $instrumento = Instrumento::findOrFail($id);
    $contexto = PermissionContext::fromUserAndResource($this->user, $instrumento);
    
    if (!$this->user->podeAcesso('instrumentos.editar_municipio', $contexto)) {
        flash('Sem permissão para editar');
        return;
    }
    // ...
}
```

### Exemplo 3: Aprovar Recurso

```php
public function aprovar(int $id): void
{
    $instrumento = Instrumento::findOrFail($id);
    
    // Verificar permissão
    if (!$this->user->podeAcesso('instrumentos.aprovar')) {
        flash('Sem permissão para aprovar');
        return;
    }
    
    // Aprovar
    $instrumento->update(['estado' => 'aprovado']);
    
    // Log
    AuditoriaService::criar($instrumento, 'Aprovado por ' . $this->user->name);
}
```

### Exemplo 4: Exportar com Filtros por Role

```php
public function exportar(): void
{
    $query = Instrumento::query();
    
    // Municipal vê apenas o seu
    if ($this->user->isMunicipal()) {
        $query->where('municipio_id', $this->user->municipio_id);
    }
    
    // Provincial vê sua província
    elseif ($this->user->isProvincial()) {
        $municipios = Municipio::where('provincia_id', $this->user->provincia_id)
            ->pluck('id');
        $query->whereIn('municipio_id', $municipios);
    }
    
    // Controlar por permissão
    if (!$this->user->podeAcesso('instrumentos.exportar')) {
        flash('Sem permissão para exportar');
        return;
    }
    
    // Exportar...
}
```

### Exemplo 5: Dashboard com Widgets Condicionais

```php
public function render()
{
    return view('livewire.dashboard', [
        'temLinkUsuarios' => $this->user->podeAcesso('usuarios.gerir'),
        'temLinkAuditoria' => $this->user->podeAcesso('auditoria.ver_completa'),
        'temLinkBackups' => $this->user->podeAcesso('backups.gerir'),
        'temLinkAnalises' => $this->user->podeAcesso('analises.acessar'),
    ]);
}
```

---

## 🔄 Migração de V1 para V2

### Compatibilidade Total

Todos os métodos V1 ainda funcionam:
```php
// V1 - Continua a funcionar
$user->temPermissao('instrumentos.ver')
$user->temAlgumaPermissao(['...', '...'])
$user->nivelMinimo()
$user->isInotu()
$user->isProvincial()
$user->isAdminMunicipal()
$user->isMunicipal()
$user->podeEditarDados()
$user->podeVerMunicipio(5)
$user->podeEditarMunicipio(5)
$user->podeGerirUsuarios()
$user->todasPermissoes()
```

### Migrar Código Gradualmente

```php
// V1
if ($user->podeEditarDados()) {
    // Algo editável

// Migrar para V2
if ($user->podeAcesso('instrumentos.editar_municipio')) {
    // Mais específico
}

// V1
if ($user->isInotu()) {
    // Acesso admin

// Migrar para V2
if ($user->podeAcesso('sistema.gerir_configuracoes')) {
    // Mais específico
}
```

### Novos Métodos Recomendados

```
temPermissao()              →  podeAcesso()
temAlgumaPermissao()        →  (igual, mantém)
temTodasAsPermissoes()      →  (novo método)
podeEditarDados()           →  podeAcesso('tipo.editar_municipio')
todasPermissoes()           →  permissoesEfetivas() ou nomesPermissoes()
podeVerRecurso()            →  (novo método)
podeEditarRecurso()         →  (novo método)
```

---

## ❓ FAQ

### P: Como adicionar uma nova permissão?

R:
```php
// 1. Adicionar ao seeder (RolesPermissoesSeederV2)
['nome' => 'novo_modulo.acao', 'descricao' => 'Descrição', 'grupo' => 'Grupo']

// 2. Atribuir às roles necessárias
$role->permissoes()->syncWithoutDetaching([
    $permissoesCriadas['novo_modulo.acao']->id,
    // ...
]);

// 3. Usar no código
$user->podeAcesso('novo_modulo.acao')
```

### P: Qual é a diferença entre ".ver", ".editar", ".editar_municipio"?

R:
- `.ver` - Ver qualquer recurso (sem restrição geográfica)
- `.editar` - Editar qualquer recurso (sem restrição)
- `.editar_municipio` - Editar apenas o do seu município
- `.editar_proprio` - Editar apenas o que criou

### P: O cache causa problema se eu mudar permissões de um utilizador?

R: Não, desde que chame `$user->invalidarCachePermissoes()` após a mudança.

### P: Como tessar as permissões?

R:
```php
// Em testes
public function test_user_pode_criar_instrumento()
{
    $user = User::factory()
        ->has(Role::factory()->state(['nome' => 'tecnico_ordenamento']))
        ->create();
    
    $this->assertTrue($user->podeAcesso('instrumentos.criar'));
}

// Com mock
$this->mock(AuthorizationService::class, function ($mock) {
    $mock->shouldReceive('podeAcesso')
        ->andReturn(true);
});
```

### P: Posso usar V1 e V2 ao mesmo tempo?

R: Sim! 100% compatível. Migre gradualmente.

### P: A auditoria tem impacto de performance?

R: Mínimo. Os registos são síncronos mas não bloqueiam o fluxo. Use queue para logging assíncrono se necessário.

### P: Como dar acesso a "apenas o seu próprio recurso"?

R:
```php
$user->podeAcesso('instrumentos.editar_proprio', $contexto)

// Ou automático via PermissionContext
$ctx = PermissionContext::fromUserAndResource($user, $instrumento);
// Se $instrumento->criado_por === $user->id, eh_criador será true
```

### P: Posso criar roles customizadas?

R: Sim! Crie via UI/API:
```php
$role = Role::create([
    'nome' => 'revisor_custom',
    'descricao' => 'Meu papel customizado',
    'grupo' => 'Customizado',
    'nivel' => 4,
]);

$role->permissoes()->attach([...]);
```

### P: Como limpar o cache de permissões para todos?

R:
```php
// Limpar cache Redis/Memcached
\Illuminate\Support\Facades\Cache::flush();

// Ou específico
foreach (User::all() as $user) {
    $user->invalidarCachePermissoes();
}
```

---

## 📞 Suporte

Para dúvidas sobre o sistema, consulte:
1. Esta documentação
2. Código do `AuthorizationService`
3. Exemplos no `RolesPermissoesSeederV2`
4. Testes em `tests/Feature/AuthorizationTest.php`

---

**Versão**: 2.0  
**Data**: 2026-04-10  
**Status**: Produção
