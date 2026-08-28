# FATURA DO PROJECTO G.TERRAS
## Instituto Nacional de Ordenamento do Território e Urbanismo (INOTU)

**Data de Emissão:** 3 de Abril de 2026  
**Número da Fatura:** INV-2026-001

---

## 📋 FUNCIONALIDADES IMPLEMENTADAS

### 1. Gestão Territorial
- Organização hierárquica: Província → Município → Comuna
- Instrumentos de ordenamento do território e urbanísticos
- Cartas municipais
- Controlo de loteamentos urbanos com registo de lotes

### 2. Processos Fundiários
- Registo, tramitação e emissão de pareceres
- Rastreabilidade completa do fluxo processual
- Documentos com QR Code para verificação digital
- Sistema de pareceres técnicos integrado

### 3. Operações Urbanísticas
- Croquis elaborados
- Gestão de terrenos ociosos
- Gestão de pareceres técnicos
- Licenciamento e fiscalização de operações

### 4. Projectos de Requalificação
- Gestão do Projecto SONA
- Outros projectos de requalificação urbana
- Rastreamento e monitorização de projectos

### 5. Ambiente de Trabalho
- Gestão colaborativa de ficheiros
- Suporte para DWG e Shapefile
- Sistema de bloqueio/desbloqueio de ficheiros
- Histórico de versões de ficheiros
- Notificações de actualização

### 6. Auditoria & Relatórios
- Registo completo de todas as acções (log de acesso)
- Trilha de auditoria por utilizador
- Monitorização e análise de dados
- Relatórios personalizáveis

### 7. Autenticação & Segurança
- Autenticação com verificação de e-mail
- Autenticação de dois factores (2FA) com códigos de recuperação
- Recuperação de palavra-passe segura
- Controlo de acesso baseado em papéis e permissões
- Sessão criptografada com electron-store

### 8. Aplicação Desktop (Desktop App)
- Suite G.TERRAS Desktop (Electron)
- Autenticação sincronizada (site ↔ desktop)
- Suporte completo **offline** com sincronização automática
- Ambiente de trabalho com acesso a ficheiros locais
- Sistema de bloqueio com PIN multi-utilizador
- Notificações nativas do SO

### 9. Cobertura Geográfica
- Sistema operacional para os **22 municípios** da Província da Huíla
- Suporte completo para todas as comunas
- Base de dados centralizada e sincronizada

---

## 💰 CÁLCULO FINANCEIRO

| Item | Valor |
|---|---|
| **Valor Orçado do Projecto** | 350.000,00 KZA |
| **(–) Desconto Aplicado + Auxílio** | (110.000,00 KZA) |
| **SALDO A PAGAR** | **240.000,00 KZA** |

### Modalidade de Pagamento — Duas Prestações

O saldo total de **240.000,00 KZA** será pago em duas prestações:

#### **1ª Prestação (60%)**
- **Valor:** 144.000,00 KZA
- **Pagável:** Na assinatura do contrato

#### **2ª Prestação (40%)**
- **Valor:** 96.000,00 KZA
- **Pagável:** Em 30 dias após a 1ª prestação

---

## 🛠️ STACK TECNOLÓGICO

| Componente | Tecnologia |
|---|---|
| **Backend** | Laravel 12 · PHP 8.4 |
| **Frontend** | Livewire 4 · Flux UI 2.9 · Tailwind CSS 4 |
| **Base de Dados** | SQLite (modo WAL para performance) |
| **Build Tool** | Vite 7 · Node.js 22 |
| **Testes** | Pest 4 · PHPUnit |
| **Autenticação** | Laravel Fortify (2FA, verificação de email) |
| **Desktop App** | Electron 33 · electron-builder 25 |
| **Linting** | Laravel Pint |

---

## 📦 MÓDULOS DO SISTEMA

### Módulos Livewire Implementados

1. **AmbienteTrabalho** — Gestão de ficheiros de trabalho
2. **CartasMunicipais** — Cartas geográficas por município
3. **CroquisElaborados** — Croquis e desenhos técnicos
4. **Dashboard** — Painel de controlo principal
5. **GestaoAuditoria** — Auditoria e logs do sistema
6. **GestaoInstrumentos** — Gestão de instrumentos urbanos
7. **GestaoLoteamentos** — Gestão de loteamentos
8. **GestaoOperacoes** — Operações urbanísticas
9. **GestaoOutrosProjectos** — Outros projectos
10. **GestaoPareceres** — Pareceres técnicos
11. **GestaoProjectoSona** — Projecto SONA específico
12. **GestaoUsuarios** — Gestão de utilizadores
13. **InstrumentoDetalhe** — Vista detalhada de instrumentos
14. **InstrumentosOrdenamento** — Ordenamento territorial
15. **InstrumentosTipo** — Tipos de instrumentos
16. **InstrumentosUrbanisticos** — Instrumentos urbanísticos
17. **ListaMunicipios** — Lista de 22 municípios
18. **LoteamentoDetalhe** — Detalhes de loteamentos
19. **LoteamentosMunicipio** — Loteamentos por município
20. **MunicipioShow** — Vista municipal detalhada
21. **NotificacaoCentro** — Centro de notificações
22. **OperacoesUrbanisticas** — Operações urbanas
23. **PainelSistema** — Painel de administração
24. **ParecerDetalhe** — Detalhes de pareceres
25. **PerfilOverview** — Perfil do utilizador
26. **ProjectosRequalificacao** — Projectos de requalificação
27. **TerrenosOciosos** — Gestão de terrenos ociosos

---

## 🔐 SEGURANÇA

✓ **Autenticação forte** — 2FA com recuperação  
✓ **Criptografia** — Dados sensíveis encriptados  
✓ **Acesso controlado** — Permissões por papel (RBAC)  
✓ **Auditoria completa** — Trilha de todas as acções  
✓ **Validação de entrada** — Proteção contra SQL injection  
✓ **CORS e CSP configurados** — Segurança web  
✓ **Sessão segura** — Electron store criptografado  

---

## 🌍 COBERTURA

- **22 Municípios da Huíla:** Menongue, Lubango, Ondjiva, etc.
- **Suporte offline** completo no app desktop
- **Sincronização automática** quando online

---

## 📄 COMO ACEDER À FATURA

A fatura em HTML está disponível em:  
**URL:** `http://gterra.inotuhuila.com/fatura-projecto`

### Para gerar PDF:
1. Abra a URL no navegador
2. Pressione `Ctrl+P` ou use o menu "Imprimir"
3. Selecione "Guardar como PDF"
4. Salve o ficheiro com o nome `G.TERRAS_Fatura_2026.pdf`

---

## ✅ NOTAS

- Sistema completamente funcional com 27 componentes Livewire
- Testes automatizados com Pest 4
- Configuração de produção em Hostgator
- Suporte multi-utilizador com sincronização
- Protocolo deep link `gterras://` registado no Windows
- Performance otimizada com caching e indexed queries

---

**Emitido em:** 3 de Abril de 2026  
**Por:** INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo  
**Projecto:** G.TERRAS — Base de Dados Provincial de Gestão de Terras

