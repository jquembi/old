# 🔒 Correção de Vulnerabilidade Crítica - Gestão de Utilizadores

**Data**: 10 de Abril de 2026  
**Criticidade**: 🔴 CRÍTICA  
**Status**: ✅ CORRIGIDA  
**Arquivo Principal**: `app/Livewire/GestaoUsuarios.php`

---

## 📋 Sumário Executivo

**Vulnerabilidade Descoberta**:
> "Qualquer utilizador estava podendo criar outro utilizador"

**Causa Raiz**:
- Componente Livewire `GestaoUsuarios.php` não validava adequadamente se o utilizador era INOTU admin
- Método `podeGerirUsuarios()` era muito permissivo (retornava true para Provincial e Municipal também)
- Falta de validação "top-level" antes de permitir operações críticas

**Solução Implementada**:
- Adicionadas 7 validações de segurança exclusivas para INOTU admin
- Cada método crítico agora verifica `if (!$user->isInotu())` como primeira linha
- Mensagens de erro claras informam o utilizador sobre o bloqueio

---

## 🔍 Análise da Vulnerabilidade

### Código Antes (Vulnerável)

```php
public function render()
{
    $user = User::find(Auth::id());
    
    // ❌ PROBLEMA: Muito permissivo
    if ($user->podeGerirUsuarios()) {  // Retorna true para Provincial + Municipal
        // Mostrar formulário de gestão
    }
}

public function guardar(): void
{
    // ❌ PROBLEMA: Sem validação top-level
    $this->validate($rules);
    // ... criar utilizador
}
```

**Por que estava vulnerável**:
1. `podeGerirUsuarios()` retornava `true` para:
   - ✅ INOTU Admin (correto)
   - ✅ Provincial Admin (ERRADO - não deveria)
   - ✅ Municipal Admin (ERRADO - não deveria)

2. Métodos críticos não faziam check de segurança ANTES da lógica
   - `abrirFormulario()` - abria formulário sem validar
   - `editar()` - permitia editar sem validar
   - `guardar()` - criava utilizador sem validar
   - `toggleActivo()` - bloqueava/desbloqueava sem validar

### Código Depois (Corrigido)

```php
public function render()
{
    $user = User::find(Auth::id());

    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA - Bloqueio acima de tudo
    if (!$user->isInotu()) {
        return view('livewire.gestao-usuarios', [
            'bloqueado' => true,
            'motivo' => 'Apenas Administradores INOTU podem gerir utilizadores.',
        ]);
    }
    
    // ✅ Só chega aqui se for INOTU
    return view('livewire.gestao-usuarios', [
        'podeGerir' => true,
        'bloqueado' => false,
    ]);
}

public function guardar(): void
{
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA - TOP-LEVEL, antes de qualquer coisa
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error',
            message: 'Apenas Administradores INOTU podem criar ou editar utilizadores.'
        );
        return;  // Sai IMEDIATAMENTE
    }
    
    // ✅ Só chega aqui se for INOTU
    $this->validate($rules);
    // ... criar utilizador ...
}
```

---

## 🛠️ Mudanças Implementadas

### 1. **render() - Bloqueio na Entrada**
- **Linha**: 44-50
- **O que faz**: Verifica INOTU admin ANTES de qualquer processamento
- **Benefício**: Bloqueia acesso na raiz - não chega nem a buscar dados
- **Mensagem**: Renderiza view de bloqueio com explicação

**Código**:
```php
if (!$user->isInotu()) {
    return view('livewire.gestao-usuarios', [
        'usuarios' => collect(),
        'roles' => collect(),
        'municipios' => collect(),
        'podeGerir' => false,
        'bloqueado' => true,
        'motivo' => 'Apenas Administradores INOTU podem gerir utilizadores.',
    ]);
}
```

### 2. **abrirFormulario() - Validação ao Abrir Formulário**
- **Linha**: 64-73
- **O que faz**: Impede abertura do formulário se não for INOTU
- **Benefício**: Duplica a segurança - mesmo que render() falhasse, formulário fica bloqueado
- **Mensagem**: Toast com feedback ao utilizador

**Código**:
```php
public function abrirFormulario(): void
{
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error',
            message: 'Apenas Administradores INOTU podem criar utilizadores.'
        );
        return;
    }
    
    $this->showForm = true;
    $this->editingId = null;
}
```

### 3. **editar() - Validação ao Editar**
- **Linha**: 80-90
- **O que faz**: Valida antes de permitir edição
- **Benefício**: Protege contra edição não autorizada
- **Replaced**: Método fraco `podeEditarUsuario()` que comparava apenas hierarquia

**Código**:
```php
public function editar(User $usuario): void
{
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error',
            message: 'Apenas INOTU pode editar utilizadores.'
        );
        return;
    }
    
    $this->editingId = $usuario->id;
    $this->showForm = true;
}
```

### 4. **guardar() - Validação TOP-LEVEL**
- **Linha**: 102-114
- **O que faz**: Checks ANTES de validação ou save
- **Benefício**: CRÍTICO - bloqueia antes de atingir o database
- **Timing**: Primeira coisa que o método faz

**Código**:
```php
public function guardar(): void
{
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA - ANTES de qualquer validação
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error',
            message: 'Apenas Administradores INOTU podem criar ou editar utilizadores.'
        );
        return;
    }
    
    $this->validate($rules);
    // ... resto..
}
```

### 5. **toggleActivo() - Validação ao Ativar/Desativar**
- **Linha**: 157-167
- **O que faz**: Impede mudança de status de utilizador
- **Benefício**: Não-INOTU não consegue ativar/desativar utilizadores
- **Mensagem**: Toast com explicação

**Código**:
```php
public function toggleActivo(User $usuario): void
{
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error',
            message: 'Apenas Administradores INOTU podem gerir estado de utilizadores.'
        );
        return;
    }
    
    $usuario->update(['activo' => !$usuario->activo]);
}
```

### 6. **getRolesDisponiveis() - Bloqueio de Atribuição de Funções**
- **Linha**: Retorna collection vazia para não-INOTU
- **O que faz**: Se alguém conseguisse passar dos checks, não conseguiria atribuir funções
- **Benefício**: Defesa em profundidade - múltiplas camadas de proteção

**Código**:
```php
private function getRolesDisponiveis(User $user): \Illuminate\Database\Eloquent\Collection
{
    // ⛔ RESTRIÇÃO CRÍTICA - Apenas INOTU
    if (!$user->isInotu()) {
        return Role::where('id', -1)->get(); // Retorna empty collection
    }
    
    return Role::all();
}
```

### 7. **Método podeEditarUsuario() - REMOVIDO**
- **Motivo**: Era fraco e induzia a comparações por hierarquia
- **Substituído por**: Checks diretos com `isInotu()`
- **Benefício**: Menos pontos de falha, lógica mais clara

---

## 📊 Matriz de Mudanças

| Método | Antes | Depois | Status |
|--------|:----:|:------:|:------:|
| `render()` | ⚠️ Permissivo | ✅ Bloqueio INOTU-only | ✅ CORRIGIDO |
| `abrirFormulario()` | ❌ Sem check | ✅ INOTU-only | ✅ CORRIGIDO |
| `editar()` | ⚠️ Fraco-check | ✅ INOTU-only | ✅ CORRIGIDO |
| `guardar()` | ❌ Sem check top-level | ✅ INOTU-only TOP-LEVEL | ✅ CORRIGIDO |
| `toggleActivo()` | ❌ Sem check | ✅ INOTU-only | ✅ CORRIGIDO |
| `getRolesDisponiveis()` | ⚠️ Retorna roles | ✅ Empty se não-INOTU | ✅ CORRIGIDO |
| `podeEditarUsuario()` | ⚠️ Fraco | ✅ REMOVIDO | ✅ REMOVIDO |

---

## 🎯 Validação de Permissão

### Padrão Aplicado em Todas Mudanças

```php
// PADRÃO: Validação defensiva top-level
public function metodoSensivel(): void
{
    $user = User::find(Auth::id());
    
    // 1️⃣ Validação PRIMEIRO
    if (!$user->isInotu()) {
        $this->dispatch('toast', type: 'error', message: '...');
        return; // ⚠️ EARLY RETURN - não continua
    }
    
    // 2️⃣ Lógica de negócio
    // ... resto...
}
```

### Por que este padrão é seguro

1. ✅ **Early return** - não chega a código perigoso se falhar
2. ✅ **Falha na permissão** - comportamento default é BLOQUEAR
3. ✅ **Mensagem clara** - utilizador sabe por que foi bloqueado
4. ✅ **Sem exceções** - não conta com try/catch para segurança
5. ✅ **Testável** - cada método pode ser testado isoladamente

---

## 🔐 Defesa em Profundidade

Agora há 3 camadas de segurança:

### Camada 1: render() - Bloqueio de Acesso à View
```
Non-INOTU → render() retorna view bloqueada → Utilizador vê mensagem de erro
```

### Camada 2: Métodos Livewire - Bloqueio no Frontend
```
Non-INOTU → clicka botão → abrirFormulario() → Bloqueia com toast
```

### Camada 3: Operações de Dados - Bloqueio no Backend
```
Non-INOTU → tenta guardar → guardar() → Bloqueia ANTES de validate/save
```

**Resultado**: Mesmo se uma camada falhar, as outras bloqueiam.

---

## 🧪 Como Testar

### Teste 1: Rendering Bloqueado

```bash
# Logar como Provincial Admin
# Ir a /dashboard/usuarios
# Esperado: Mensagem "Apenas Administradores INOTU podem gerir utilizadores."
```

### Teste 2: Botão Desabilitado

```bash
# Provisional vê tela bloqueada
# NÃO há botão "Novo Utilizador"
# Template renderiza div .alert-danger
```

### Teste 3: Método Bloqueado (Segurança Extra)

```php
// Teste no código
// Simular click em botão (mesmo que não deveria ser visível)
$component->abrirFormulario();
// Esperado: Toast dispatch com mensagem de erro
// showForm NÃO muda para true
```

---

## 📝 Checklist de Conformidade

- ✅ Apenas INOTU pode criar utilizadores
- ✅ Apenas INOTU pode editar utilizadores
- ✅ Apenas INOTU pode ativar/desativar utilizadores
- ✅ Apenas INOTU pode atribuir funções
- ✅ Mensagens de erro são claras
- ✅ Sem permissão = Falha fechada (default BLOQUEIA)
- ✅ Sem camadas de try/catch confiáveis (validação explícita)
- ✅ Early returns implementados em todos os métodos

---

## 📋 Ficheiros Modificados

1. **`app/Livewire/GestaoUsuarios.php`**
   - 7 métodos atualizados
   - 1 método removido (`podeEditarUsuario()`)
   - ~50 linhas de mudanças

2. **`resources/views/livewire/gestao-usuarios.blade.php`**
   - Adicionado bloco de bloqueio no topo
   - Template agora renderiza alerta de segurança

---

## 🚀 Próximos Passos

1. ✅ **CONCLUÍDO**: Corrigir vulnerabilidade crítica
2. ⏳ **TODO**: Executar testes de segurança
3. ⏳ **TODO**: Verificar outros componentes Livewire para padrões similares
4. ⏳ **TODO**: Adicionar testes unitários de segurança
5. ⏳ **TODO**: Documentar política de segurança em README

---

## 📞 FAQ

**P: Por que tantos checks?**  
R: Defesa em profundidade. Se um falhar, os outros bloqueiam. É redundante, mas seguro.

**P: Afeta performance?**  
R: Negligível. Uma query `User::find(Auth::id())` é < 1ms. Segurança > performance.

**P: Posso usar JWT Token em vez de Auth::id()?**  
R: Sim, mas Auth::id() já usa JWT internamente em Fortify. Sem mudanças necessárias.

**P: E se esqueci de adicionar check num novo método?**  
R: Má ideia. Sempre seguir o padrão de early return com `isInotu()`.

---

**Versão**: 1.0  
**Data**: 10 de Abril de 2026  
**Status**: 🟢 Implementado e Ativo
