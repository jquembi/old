# Instalação e configuração do G.TERRAS

## Objectivo

A instalação prepara uma instância do G.TERRAS sem criar contas conhecidas, sem carregar processos fictícios e sem apagar dados existentes. A configuração é persistida no ficheiro \`.env\` e as migrations aplicam alterações estruturais pendentes.

## Requisitos

- PHP 8.2 ou superior e Composer.
- Extensões PHP obrigatórias: PDO, mbstring, OpenSSL, Fileinfo, Ctype, JSON, Tokenizer e XML.
- Um driver PDO suportado: SQLite, MySQL ou MariaDB.
- Permissão de escrita em \`.env\`, \`storage\`, \`bootstrap/cache\` e no directório da base SQLite, quando aplicável.
- Dependências PHP instaladas com \`composer install\`.

O assistente apresenta os drivers realmente disponíveis no servidor. PostgreSQL não é disponibilizado para novas instalações até a compatibilidade integral de todas as migrations ser validada.

## Assistente web

Abrir \`/instalar\` e seguir oito etapas:

1. Diagnóstico dos requisitos e do estado real da base.
2. Motor, caminho/nome da base, host, porta, utilizador e teste de leitura/escrita.
3. Nome, URL, ambiente, idioma, país, domínio e fuso horário.
4. Modalidade Núcleo Central ou Node Provincial e província seleccionada.
5. Parâmetros SMTP opcionais.
6. Conta do primeiro administrador.
7. Revisão dos parâmetros e confirmação explícita.
8. Execução e validação das etapas.

A palavra-passe da base não é devolvida ao navegador. Quando o campo fica vazio, o instalador reutiliza a credencial já configurada apenas se motor, host, porta e utilizador coincidirem. As palavras-passe não aparecem no resumo nem nos logs técnicos.

## Dados fundamentais

O \`SystemCoreSeeder\` é o conjunto executado pela instalação normal:

- Catálogo territorial. Num Node Provincial é limitado à província escolhida.
- Papéis e permissões necessários ao controlo de acesso.
- Categorias e tipos fundamentais de instrumentos.
- Configurações-base de tipos de documento/QR.
- Workflow padrão de tramitação fundiária.

Não cria utilizadores, contas predefinidas, processos, pagamentos ou anexos de demonstração. O \`DatabaseSeeder\` chama exclusivamente o \`SystemCoreSeeder\`.

A migration de \`tenants\` cria apenas a estrutura. O tenant correspondente à modalidade e à configuração é registado pelo serviço de instalação, depois de a base estar preparada.

As permissões de documentos e RUPE, o workflow e o catálogo de funções são semeados pelo conjunto fundamental e podem ser repostos de forma idempotente. As migrations históricas de permissões e workflow já não introduzem dados-base durante \`migrate\`.

A antiga migration de funções mantém apenas a transformação de dados legados quando detecta funções/atribuições pré-existentes. Numa base nova, deixa o catálogo inicial para o seeder. As migrations que copiam atribuições antigas, acrescentam identificadores de sincronização ou vinculam registos existentes continuam a ser migrações de dados necessárias para preservar instalações anteriores.

Os utilizadores de demonstração foram separados do fluxo fundamental. O seeder de demonstração é manual e bloqueia a execução em produção.

## Segurança e recuperação

- O estado da instalação fica em \`installation_metadata\` na própria base de dados. \`storage/app/instalado.lock\` é apenas auxiliar e não determina sozinho se a aplicação está instalada.
- As alterações ao \`.env\` criam cópia de segurança com permissões restritas e substituem o ficheiro de forma atómica.
- As rotas do instalador mantêm protecção CSRF e limite de pedidos.
- As etapas são serializadas para impedir duas instalações concorrentes.
- A instalação normal bloqueia bases com utilizadores ou tabelas sem histórico coerente de migrations.
- Uma instalação interrompida mantém o estado pendente e pode ser retomada.
- A instalação e a recuperação não executam \`migrate:fresh\`, \`db:wipe\`, \`truncate\` ou remoção automática de tabelas.

Diagnosticar e reparar uma instalação existente:

\`\`\`bash
php artisan gterra:repair
\`\`\`

Confirmar a recuperação sem prompt interactivo:

\`\`\`bash
php artisan gterra:repair --force
\`\`\`

O comando aplica migrations pendentes, repõe dados fundamentais de forma idempotente e valida a existência de uma conta com o papel administrativo adequado. Se não conseguir confirmar essa conta, interrompe a recuperação e preserva os dados.

## Instalação por terminal

\`\`\`bash
php artisan gterra:install --configure
\`\`\`

Pode fornecer os dados administrativos por opções. Em produção, \`--force\` apenas autoriza a primeira instalação; nunca autoriza reinstalar sobre dados existentes.

\`\`\`bash
php artisan gterra:install \\
  --admin-name="Administrador do Sistema" \\
  --admin-email="admin@instituicao.ao" \\
  --skip-build
\`\`\`

A compilação dos assets pode ser feita depois:

\`\`\`bash
npm install
npm run build
\`\`\`

## Verificações após a instalação

\`\`\`bash
php artisan migrate:status
php artisan gterra:repair
php artisan route:list --path=instalar
php artisan test --filter=Instalador
\`\`\`

Executar testes num ambiente de desenvolvimento com as dependências instaladas. A base de produção não deve ser usada como ambiente de teste.
