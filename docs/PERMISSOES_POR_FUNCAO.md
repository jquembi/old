# 📋 Permissões por Função - O que Cada Role Pode CRIAR

**Versão**: 2.0 Sistema de Permissões INOTU  
**Data**: Abril 2026  
**Status**: ⚠️ Crítico - Restrições de Segurança Ativas

---

## 🔒 Resumo Executivo

Este documento deixa **claramente definido** o que **CADA FUNÇÃO pode CRIAR** no sistema INOTU. Foi implementado rigorosamente com:

✅ **INOTU Admin**: Acesso total a TODAS as operações de criação  
✅ **Provincial Admin**: Apenas VISUALIZA dados, sem permissão de criar  
✅ **Municipal Admin**: Cria recursos limitados ao seu município  
✅ **Técnicos**: Acesso granular por especialidade  
✅ **Operadores**: Operações de rotina, sem gestão  

---

## 1. INOTU Admin (Administrador Nacional)

| Recurso | Criar | Editar | Eliminar | Notas |
|---------|:-----:|:------:|:--------:|-------|
| **Utilizadores** | ✅ | ✅ | ✅ | Apenas INOTU pode criar/gerir utilizadores |
| **Funções/Roles** | ✅ | ✅ | ✅ | Gestão completa do sistema de funções |
| **Províncias** | ✅ | ✅ | ❌ | Manter integridade geográfica |
| **Municípios** | ✅ | ✅ | ❌ | Criar novos municípios com dados de base |
| **Instrumentos Urbanísticos** | ✅ | ✅ | ✅ | Todos os tipos, em qualquer localização |
| **Loteamentos** | ✅ | ✅ | ✅ | Nacional, municipal, urbano |
| **Operações** | ✅ | ✅ | ✅ | Planos, estudos, projetos especiais |
| **Croquis/Desenhos** | ✅ | ✅ | ✅ | Todos os formatos e tipos |
| **Pareceres Técnicos** | ✅ | ✅ | ✅ | Aprovação/desaprovação de qualquer projeto |
| **Projeto SONA** | ✅ | ✅ | ✅ | Gestão completa |
| **Outros Projetos** | ✅ | ✅ | ✅ | Projetos especiais e colaborações |
| **Cartas Municipais** | ✅ | ✅ | ✅ | Gestão cartográfica completa |
| **Auditoria** | ✅ apenas leitura | — | — | Visualizar logs, não criar entrées |
| **Configurações do Sistema** | ✅ | ✅ | ✅ | Parâmetros, variáveis, cache |

---

## 2.5 Secretaria (Gabinete - Assistente INOTU)

| Recurso | Criar | Ver | Editar | Eliminar | Notas |
|---------|:-----:|:---:|:------:|:--------:|-------|
| **Utilizadores** | ❌ | ✅ **TODOS** | ❌ | ❌ | Apenas visualiza todos os utilizadores do sistema |
| **Instrumentos Urbanísticos** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Loteamentos** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Operações** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Croquis** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Pareceres Técnicos** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Projeto SONA** | ❌ | ✅ | ❌ | ❌ | Visualização apenas |
| **Dashboard** | ✅ apenas leitura | — | — | Visualiza KPIs do sistema |
| **Auditoria** | ✅ apenas leitura | — | — | Logs de todo sistema |

> 📌 **Função**: Secretaria é assistente do INOTU. **Pode visualizar TODOS os utilizadores**, mas NÃO pode criar/editar/eliminar nada. Apenas leitura para auxiliar nas tarefas administrativas.

---

## 2. Provincial Admin (Administrador Provincial / Governo)

| Recurso | Criar | Ver | Editar | Eliminar | Notas |
|---------|:-----:|:---:|:------:|:--------:|-------|
| **Utilizadores** | ❌ **BLOQUEADO** | ✅ `*seus func.` | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ⛔ Apenas INOTU cria. Vê apenas seus funcionários da sua província |
| **Instrumentos Urbanísticos** | ✅ | ✅ `*sua_provincia` | ✅ `*sua_provincia` | ❌ | Visualiza, mas criação requer aprovação INOTU |
| **Loteamentos** | ✅ | ✅ `*sua_provincia` | ✅ `*sua_provincia` | ❌ | Sem permissão de eliminar |
| **Operações** | ✅ | ✅ `*sua_provincia` | ✅ `*sua_provincia` | ❌ | Propostas, candidatas a aprovação |
| **Croquis** | ✅ | ✅ `*sua_provincia` | ✅ `*sua_provincia` | ❌ | Desenhos preparatórios |
| **Pareceres Técnicos** | ✅ | ✅ | ✅ | ❌ | Emite parecer técnico provincial |
| **Cartas Municipais** | ✅ | ✅ | ✅ | ❌ | Cartografia provincial |
| **Projeto SONA** | ✅ | ✅ | ✅ | ❌ | Dados de base, sem finalização |
| **Dashboard** | ✅ apenas leitura | — | — | Visualiza KPIs da sua província |
| **Auditoria** | ✅ apenas leitura | — | — | Logs da sua província apenas |

> 📌 **Nota Critical**: Provincial PODE criar recursos (para propostas), mas a **ELIMINAÇÃO requer INOTU**. Nenhuma finalisação sem aprovação nacional. **VÊ APENAS seus funcionários**.

---

## 3. Municipal Admin (Administrador Municipal / Administrações)

| Recurso | Criar | Ver | Editar | Eliminar | Notas |
|---------|:-----:|:---:|:------:|:--------:|-------|
| **Utilizadores** | ❌ **BLOQUEADO** | ✅ `*seus` | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ⛔ Apenas INOTU cria. Vê apenas seus utilizadores |
| **Instrumentos Urbanísticos** | ✅ `*seu município` | ✅ `*seu município` | ✅ `*seu município` | ❌ | Instrumentos locais (PRG, APU, etc) |
| **Loteamentos** | ✅ `*seu município` | ✅ `*seu município` | ✅ `*seu município` | ❌ | Loteamentos municipais |
| **Operações** | ✅ `*seu município` | ✅ `*seu município` | ✅ `*seu município` | ❌ | Planos e operações locais |
| **Croquis** | ✅ `*seu município` | ✅ `*seu município` | ✅ `*seu município` | ❌ | Desenhos e croquis |
| **Pareceres** | ✅ `*seu município` | ✅ `*seu município` | ✅ `*seu município` | ❌ | Parecer municipal local |
| **Cartas Municipais** | ✅ | ✅ | ✅ | ❌ | Cartografia local |
| **Dashboard** | ✅ apenas leitura | — | — | Visualiza KPIs do seu município |
| **Auditoria** | ✅ apenas leitura | — | — | Logs do seu município apenas |
| **Outros municípios** | ❌ | ❌ | ❌ | ❌ | Acesso bloqueado a outros municípios |

> 📌 **Restrição Geográfica**: Municipal Admin só pode operar dentro do seu município. Cross-municipality é **BLOQUEADO** no nível de dados. **VÊ APENAS seus utilizadores**.

---

## 4. Técnico de Ordenamento (Técnico Especializado)

| Recurso | Criar | Ver | Editar | Eliminar | Notas |
|---------|:-----:|:---:|:------:|:--------:|-------|
| **Utilizadores** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ⛔ Sem acesso NENHUM - nem visualização |
| **Instrumentos Urbanísticos** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Preparação técnica de instrumentos |
| **Loteamentos** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Análise e propostas de loteamento |
| **Operações** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Ops técnicas de planeamento |
| **Croquis** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Desenhos técnicos e esquemas |
| **Pareceres Técnicos** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Análise técnica especializada |
| **Projeto SONA** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Dados técnicos SONA |
| **Outros Projetos** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Participação em projetos |
| **Cartas/GIS** | ✅ | ✅ `*seus` | ✅ `*seus` | ❌ | Edição de camadas técnicas |

> ⛔ **CRÍTICO**: Técnicos **NÃO TÊM ACESSO** à gestão de utilizadores. Nenhuma visualização, nenhuma permissão.

---

## 5. Operador GIS (Técnico geoespacial)

| Recurso | Criar | Editar | Eliminar | Notas |
|---------|:-----:|:------:|:--------:|-------|
| **Utilizadores** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ⛔ Apenas INOTU |
| **Geometrias/Shapes** | ✅ | ✅ | ❌ | Criação exclusiva de dados geoespaciais |
| **Cartas Municipais** | ✅ | ✅ | ❌ | Edição de basemaps e camadas |
| **Croquis GIS** | ✅ | ✅ | ❌ | Desenhos georreferenciados |
| **Coordenadas** | ✅ | ✅ | ❌ | Pontos, linhas, polígonos |
| **Outros recursos** | ❌ | ❌ | ❌ | Acesso restrito a GIS/geo |

---

## 6. Operador de Auditoria

| Recurso | Criar | Editar | Eliminar | Notas |
|---------|:-----:|:------:|:--------:|-------|
| **Utilizadores** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ❌ **BLOQUEADO** | ⛔ Apenas INOTU |
| **Registos de Auditoria** | ✅ apenas leitura | — | — | Pode visualizar E compilar logs |
| **Relatórios de Auditoria** | ✅ | ✅ | ❌ | Criar e editar relatórios |
| **Dashboard de Auditoria** | ✅ apenas leitura | — | — | Acesso ao painel de auditoria |
| **Todos os outros recursos** | ❌ | ❌ | ❌ | Acesso restrito apenas a auditoria |

---

## 7. Viewer / Consultor (Apenas Visualização)

| Recurso | Criar | Editar | Eliminar | Notas |
|---------|:-----:|:------:|:--------:|-------|
| **Qualquer coisa** | ❌ | ❌ | ❌ | Acesso read-only apenas |
| **Dashboard** | ✅ apenas visualizar | — | — | Gráficos, mapas, estatísticas |
| **Relatórios** | ✅ apenas download | — | — | Exporta em PDF/XLS |
| **Auditoria** | ✅ apenas visualizar | — | — | Logs públicos apenas |

---

## ⚠️ RESTRIÇÕES CRÍTICAS DE SEGURANÇA

### Criação de Utilizadores

```
┌─────────────────────────────────────────────────────┐
│  ⛔ BLOQUEADO PARA TODOS EXCETO INOTU ADMIN         │
├─────────────────────────────────────────────────────┤
│  ✅ INOTU Admin         → Pode criar qualquer user  │
│  ❌ Provincial Admin    → BLOQUEADO                 │
│  ❌ Municipal Admin     → BLOQUEADO                 │
│  ❌ Técnicos           → BLOQUEADO                 │
│  ❌ Operadores        → BLOQUEADO                 │
│  ❌ Viewers           → BLOQUEADO                 │
└─────────────────────────────────────────────────────┘
```

**Implementação**: 
- `GestaoUsuarios.php` - linha 44-50: Validação `if (!$user->isInotu())` com bloqueio
- `GestaoUsuarios.php` - linha 64-73: Validação em `abrirFormulario()`
- `GestaoUsuarios.php` - linha 80-90: Validação em `editar()`
- `GestaoUsuarios.php` - linha 102-114: Validação em `guardar()` (TOP-LEVEL)
- `GestaoUsuarios.php` - linha 157-167: Validação em `toggleActivo()`

### Acesso a Gestão de Utilizadores

```
┌─────────────────────────────────────────────────────┐
│  Tela: /dashboard/usuarios                          │
│                                                      │
│  ✅ Acessível para: INOTU Admin apenas              │
│  ❌ Bloqueado para: Provincial, Municipal, Técnico  │
│                                                      │
│  Mensagem de erro para não-INOTU:                   │
│  "Apenas Administradores INOTU podem gerir          │
│   utilizadores."                                    │
└─────────────────────────────────────────────────────┘
```

### Restrições por Localização

**Provincial Admin**:
- Cria recursos DENTRO da sua província
- Não consegue criar na província de outro admin

**Municipal Admin**:
- Cria recursos DENTRO do seu município  
- Não consegue criar noutros municípios
- Não consegue visualizar dados de outros municípios

**Técnicos Especializados**:
- Criam recursos associados ao seu contexto
- Não conseguem atravessar limites geográficos/administrativos

---

## 📊 Matriz Resumida - Capacidade de CRIAR + VISUALIZAR

| Função | Criar Utilizadores | Ver Utilizadores | Escopo Visualização | Restrição |
|--------|:---:|:---:|:---:|:---:|
| **INOTU Admin** | ✅ | ✅ | **TODOS** | — |
| **Secretaria** | ❌ | ✅ | **TODOS** | Apenas leitura |
| **Provincial Admin** | ❌ | ✅ | **Sua Província** | Governo - seus funcionários |
| **Municipal Admin** | ❌ | ✅ | **Seu Município** | Administrações - seus utilizadores |
| **Técnico Ordenamento** | ❌ | ❌ | **NENHUM** | ⛔ Sem aceso nenhum |
| **Operador GIS** | ❌ | ❌ | **NENHUM** | ⛔ Sem aceso nenhum |
| **Operador Auditoria** | ❌ | ❌ | **NENHUM** | ⛔ Sem aceso nenhum |
| **Viewer** | ❌ | ❌ | **NENHUM** | ⛔ Sem aceso nenhum |

---

## 🔐 Como Esta Matriz É Cumprida

### 1. **Backend - PHP/Livewire** (Fonte da Verdade)

Toda a validação acontece no servidor:

```php
// GestaoUsuarios.php - Exemplo de validação crítica
public function guardar(): void {
    $user = User::find(Auth::id());
    
    // ⛔ RESTRIÇÃO CRÍTICA DE SEGURANÇA
    // INOTU-ONLY CHECK - TOP-LEVEL, antes de qualquer lógica
    if (!$user->isInotu()) {
        $this->dispatch('toast', 
            type: 'error', 
            message: 'Apenas Administradores INOTU podem criar ou editar utilizadores.'
        );
        return;
    }
    
    // Só chega aqui se INOTU
    $this->validate($rules);
    // ... criar utilizador ...
}
```

### 2. **Frontend - Blade Templates** (Convenção)

Botões e formulários respeitam as permissões:

```blade
@if($podeGerir)
    <flux:button wire:click="abrirFormulario" variant="primary">
        Novo Utilizador
    </flux:button>
@endif

@if($bloqueado)
    <div class="alert alert-danger">
        {{ $motivo ?? 'Acesso não autorizado.' }}
    </div>
@endif
```

### 3. **Database Queries** (Escopo Automático)

Queries respeitam contextos:

```php
// Provincial vê apenas sua província
$query->where('provincia_id', $user->provincia_id);

// Municipal vê apenas seu município  
$query->where('municipio_id', $user->municipio_id);
```

---

## 📋 Checklist de Conformidade

- ✅ Apenas INOTU pode criar utilizadores (verificado até 10-Apr-2026)
- ✅ Provincial não consegue gerir utilizadores (bloqueado em GestaoUsuarios.php)
- ✅ Municipal não consegue gerir utilizadores (bloqueado em GestaoUsuarios.php)
- ✅ Criação de recursos respeitaditos limites por função
- ✅ Restrições geográficas aplicadas em queries
- ✅ Mensagens de erro claras para bloqueios

---

## 🚀 Como Testar as Restrições

### Teste 1: Apenas INOTU pode criar utilizadores

```bash
# Login como Provincial Admin
$ curl -X POST /api/usuarios \
  -H "Authorization: Bearer PROVINCIAL_TOKEN" \
  -d "name=Novo Utilizador"

# Resposta esperada: 403 Forbidden
# Mensagem: "Apenas INOTU pode gerir utilizadores"

# Login como INOTU Admin  
$ curl -X POST /api/usuarios \
  -H "Authorization: Bearer INOTU_TOKEN" \
  -d "name=Novo Utilizador"

# Resposta esperada: 201 Created
```

### Teste 2: Municipal não consegue ver outros municípios

```bash
# Login como Municipal Admin de "Luanda"
# Tentar ver operações de "Huila"

# Endpoint: GET /api/operacoes?municipio=huila
# Resposta: [] (vazio ou 403)
```

### Teste 3: Provincial não consegue eliminar

```bash
# Login como Provincial Admin
# Tentar DELETE /api/municipios/123

# Resposta esperada: 403 Forbidden
```

---

## 📞 Suporte / Dúvidas

Se uma função não consegue fazer o que espera, confirme:

1. ✅ Função atribuída corretamente ao utilizador
2. ✅ Contexto geográfico correto (Prov/Mun)
3. ✅ Cache de permissões atualizado (`redis:flush-db`)
4. ✅ Sem bloqueios manuais no recurso (aprovações pendentes?)

---

**Versão**: 2.0  
**Última atualização**: 10 de Abril de 2026  
**Status**: 🟢 Ativo e Cumprido
