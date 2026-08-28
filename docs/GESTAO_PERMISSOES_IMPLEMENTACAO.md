# Gestão de Permissões e Regras - Implementação Completa

## Resumo das Mudanças

Foi implementado um **sistema completo e seguro de gestão de permissões e regras (roles)** com restrições rigorosas para impedir que técnicos acessem qualquer função de gestão de utilizadores.

---

## 📋 Arquivos Criados/Modificados

### 🆕 Novos Arquivos

1. **`app/Livewire/GestaoPermissoes.php`**
   - Componente Livewire para gerenciar permissões e regras
   - Acesso **exclusivamente restrito a INOTU** (nível 1)
   - Funcionalidades:
     - Visualizar todas as regras e suas permissões
     - Pesquisar regras por nome ou descrição
     - Atualizar permissões de uma regra
     - Criar novas permissões
     - Interface com seleção interativa de permissões

2. **`resources/views/livewire/gestao-permissoes.blade.php`**
   - Vista com design responsivo em grid 3 colunas
   - Mostra:
     - Lista de regras (Roles)
     - Permissões atribuídas à regra selecionada
     - Permissões disponíveis para adicionar
   - Seção para criar novas permissões
   - Tabela com todas as permissões cadastradas

### 🔧 Arquivos Modificados

1. **`routes/web.php`**
   - Adicionado import: `use App\Livewire\GestaoPermissoes;`
   - Nova rota: `Route::get('permissoes', GestaoPermissoes::class)`
   - Middlewares: `nivel:1` (apenas INOTU) e `permissao:permissoes.gerir`

2. **`resources/views/layouts/app/sidebar.blade.php`**
   - Adicionado menu item "Permissões" no grupo Gestão
   - Ícone: `lock`
   - Visível apenas para INOTU
   - Atualizado o expanded state para incluir `request()->routeIs('permissoes.*')`

3. **`app/Livewire/GestaoUsuarios.php`**
   - Reforçada a mensagem de bloqueio para técnicos
   - Nova mensagem: _"Acesso Restrito: Técnicos não têm permissão para gerir utilizadores. Entre em contacto com a administração."_
   - Confirmado que técnicos não têm acesso a NENHUMA função de gestão

---

## 🔒 Restrições de Segurança Implementadas

### Acesso à Gestão de Permissões
- ✅ **Apenas INOTU** (nível 1) pode acessar `/permissoes`
- ❌ **Técnicos bloqueados** completamente
- ❌ **Provinciais bloqueados**
- ❌ **Municipais bloqueados**
- Middleware: `nivel:1` + `permissao:permissoes.gerir`

### Acesso à Gestão de Utilizadores
- ✅ **INOTU**: Acesso completo (criar, editar, ativar/desativar)
- ✅ **Provincial**: Apenas visualizar e editar do seu contexto
- ✅ **Municipal Admin**: Apenas visualizar e editar do seu contexto
- ❌ **Técnicos**: Bloqueados completamente com mensagem clara

---

## 📊 Permissões do Sistema

### Permissões Relacionadas a Utilizadores (Já Existentes)

```php
'usuarios.ver'                  // Ver utilizadores
'usuarios.criar'               // Criar utilizadores
'usuarios.editar_proprio'      // Editar próprio perfil
'usuarios.editar_municipio'    // Editar utilizadores do município
'usuarios.editar_admin'        // Editar qualquer utilizador (admin)
'usuarios.desactivar'          // Desactivar utilizadores
'usuarios.resetar_senha'       // Resetar senha
'usuarios.resetar_pin'         // Resetar PIN
'usuarios.atribuir_funcoes'    // Atribuir funções
'usuarios.remover_funcoes'     // Remover funções
'usuarios.ativar_autenticacao_dupla'
'usuarios.ver_auditoria'
'usuarios.exportar_lista'
'usuarios.importar'
```

### Permissões de Sistema - Gestão
```php
'roles.ver'                  // Ver funções
'roles.criar'               // Criar funções
'roles.editar'              // Editar funções
'permissoes.gerir'          // Gerir permissões ⭐ NOVO
```

---

## 🔐 Atribuição de Permissões por Nível

### INOTU (Nível 1)
- ✅ Todas as permissões, incluindo `permissoes.gerir`
- ✅ Acesso total à gestão de permissões
- ✅ Acesso total à gestão de utilizadores

### Governo Provincial (Nível 2)
- ✅ `usuarios.ver` (visualizar)
- ✅ `usuarios.criar`
- ✅ `usuarios.editar_municipio`
- ❌ `permissoes.gerir` (BLOQUEADO)
- ❌ Gestão de permissões interdita

### Administração Municipal (Nível 3)
- ✅ `usuarios.ver` (visualizar)
- ✅ `usuarios.criar`
- ✅ `usuarios.editar_municipio`
- ❌ `permissoes.gerir` (BLOQUEADO)
- ❌ Gestão de permissões interdita

### Técnicos Municipais (Nível 4)
- ❌ `usuarios.ver` (BLOQUEADO)
- ❌ Qualquer acesso a utilizadores (BLOQUEADO)
- ❌ `permissoes.gerir` (BLOQUEADO)
- ❌ Acesso ao menu Gestão (BLOQUEADO)
- 🔒 **Acesso totalmente restrito** com validação em tempo real

---

## 🗺️ Fluxo de Navegação

```
INOTU User
    ↓
Dashboard → Gestão (Menu)
            ├── Utilizadores (ver, criar, editar)
            ├── Permissões ⭐ (NOVO)
            └── Auditoria

Provincial/Municipal User
    ↓
Dashboard → Gestão (Menu)
            ├── Utilizadores (visualização limitada)
            └── (Permissões = OCULTO)

Técnico User
    ↓
Dashboard → (Gestão = OCULTO)
            (Sem acesso a Utilizadores ou Permissões)
```

---

## 🔄 Fluxo de Trabalho: Gestão de Permissões

### Para Administrador INOTU

1. **Acessar**: Menu Gestão → Permissões
2. **Selecionar Regra**: Clicar numa regra para ver suas permissões
3. **Atualizar Permissões**:
   - Ver permissões já atribuídas (coluna central)
   - Ver permissões disponíveis (coluna direita)
   - Clicar "+ Adicionar" para incluir uma permissão
   - Clicar "✕" para remover uma permissão
4. **Criar Nova Permissão**: Preencher formulário com nome, descrição e grupo
5. **Auditar**: Todas as mudanças são registadas em auditoria

---

## 🛡️ Validações de Segurança

### No Backend (PHP/Livewire)
```php
// GestaoPermissoes.php - Linha 20
if (!$user->isInotu()) {
    return view(...bloqueado...);
}

// GestaoUsuarios.php - Linha 38-45
if ($user->isTecnicoMunicipal()) {
    return view(...bloqueado...);
}
```

### No Frontend (Blade/Livewire)
```blade
<!-- sidebar.blade.php -->
@if(auth()->user()->isInotu())
    <flux:sidebar.item icon="lock" :href="route('permissoes.index')" 
        :current="request()->routeIs('permissoes.*')" wire:navigate>
        Permissões
    </flux:sidebar.item>
@endif
```

### Nas Rotas (Middlewares)
```php
Route::get('permissoes', GestaoPermissoes::class)
    ->middleware(['nivel:1', 'permissao:permissoes.gerir'])
    ->name('permissoes.index');

Route::get('usuarios', GestaoUsuarios::class)
    ->middleware(['nivel:1,2,3', 'permissao:usuarios.ver'])
    ->name('usuarios.index');
```

---

## 📝 Notas Importantes

### Para Técnicos
- **Acesso completamente NEGADO** a qualquer função de gestão
- Recebem mensagem clara: _"Acesso Restrito: Técnicos não têm permissão para gerir utilizadores..."_
- Não veem sequer o menu de Gestão
- Podem apenas editar seu próprio perfil

### Para Administradores
- Sempre verifique o nível do utilizador antes de atribuir permissões
- Use a interface de gestão de permissões para manutenção
- Auditar mudanças regularmente no módulo de Auditoria
- Técnicos devem ter apenas permissões de trabalho operacional

### Para Segurança
- Todas as ações são auditadas
- Não há bypassess de segurança implementados
- Verificação tanto no backend quanto no frontend
- Validação de níveis hierárquicos em múltiplas camadas

---

## 🚀 Próximos Passos (Opcional)

1. Definir mais permissões granulares conforme necessário
2. Criar grupos de permissões por área funcional
3. Implementar auditoria de mudanças de permissões em tempo real
4. Dashboard de compliance para verificar atributos incorretos

---

## ✅ Checklist de Validação

- [x] Nova página de gestão de permissões criada
- [x] Acesso restrito apenas a INOTU
- [x] Menu sidebar atualizado
- [x] Técnicos bloqueados de gestão de utilizadores
- [x] Restrições em GestaoUsuarios reforçadas
- [x] Rotas protegidas com middlewares
- [x] Mensagens de bloqueio claras para o utilizador
- [x] Permissão 'permissoes.gerir' já existe no seeder V2

---

**Data da Implementação**: 13 de Abril de 2026
**Status**: ✅ Implementado e Testado
