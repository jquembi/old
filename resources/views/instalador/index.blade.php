<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Instalação do G.TERRAS — INOTU</title>
<style>
:root{--red:#9f1734;--red2:#c52b4d;--ink:#18202b;--muted:#687386;--line:#e5e9ef;--paper:#fff;--canvas:#f4f6f9;--gold:#c5a46d;--ok:#16764a;--warn:#9a6514;--bad:#b42336}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 8% 0%,#fff 0,transparent 36%),var(--canvas);color:var(--ink);font:14px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
.top{background:#171d27;color:#fff;border-bottom:3px solid var(--gold)}.top-inner{max-width:1320px;margin:auto;padding:20px 26px;display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{display:flex;align-items:center;gap:14px}.mark{width:44px;height:44px;border:1px solid #ffffff30;border-radius:13px;display:grid;place-items:center;background:linear-gradient(145deg,#b62443,#76132b);font-weight:900;letter-spacing:-2px;font-size:18px}.brand strong{display:block;font-size:17px;letter-spacing:.03em}.brand small{color:#b9c0cb;font-size:11px;letter-spacing:.14em;text-transform:uppercase}.meta{display:flex;gap:12px;color:#c5cad2;font-size:12px}.pill{border:1px solid #ffffff25;border-radius:30px;padding:6px 11px}
main{max-width:1320px;margin:30px auto 60px;padding:0 22px}.shell{display:grid;grid-template-columns:285px minmax(0,1fr);gap:22px;align-items:start}.sidebar,.panel{background:var(--paper);border:1px solid var(--line);border-radius:18px;box-shadow:0 14px 40px #18202b0e}.sidebar{padding:22px;position:sticky;top:18px}.sidebar h2{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin:0 0 16px}.step-list{display:grid;gap:5px}.step-nav{width:100%;border:0;background:transparent;padding:11px 10px;display:flex;align-items:center;gap:11px;text-align:left;border-radius:12px;color:#697486;cursor:pointer;font:inherit}.step-nav:disabled{cursor:default;opacity:.52}.step-nav .n{width:27px;height:27px;flex:0 0 27px;border-radius:9px;background:#f0f2f5;display:grid;place-items:center;font-size:11px;font-weight:800}.step-nav.active{background:#fbf0f2;color:var(--red);font-weight:700}.step-nav.active .n{background:var(--red);color:white}.step-nav.done{color:var(--ok)}.step-nav.done .n{background:#e7f5ed;color:var(--ok)}.side-note{margin-top:22px;padding:14px;background:#f8f7f4;border:1px solid #eee8dc;border-radius:13px;color:#77664b;font-size:12px}.side-note strong{display:block;color:#5e4b2e;margin-bottom:4px}
.content{min-width:0}.intro{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin:2px 3px 18px}.eyebrow{color:var(--red);font-size:11px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}.intro h1{font-size:26px;line-height:1.2;letter-spacing:-.035em;margin:5px 0}.intro p{margin:0;color:var(--muted);max-width:650px}.progress-text{font-size:12px;color:var(--muted);white-space:nowrap}.panel{padding:30px;display:none;min-height:560px}.panel.active{display:block;animation:rise .22s ease-out}@keyframes rise{from{opacity:.5;transform:translateY(5px)}to{opacity:1;transform:none}}
.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:24px}.panel-head h2{font-size:21px;letter-spacing:-.025em;margin:0 0 5px}.panel-head p{color:var(--muted);margin:0;max-width:750px}.step-number{width:38px;height:38px;border-radius:12px;background:#fbf0f2;color:var(--red);display:grid;place-items:center;font-weight:800}
.welcome{display:grid;grid-template-columns:1.15fr .85fr;gap:26px;align-items:center;padding:14px 0 22px}.welcome h2{font-size:33px;line-height:1.12;letter-spacing:-.045em;margin:0 0 14px;max-width:600px}.welcome p{font-size:15px;color:var(--muted);max-width:630px}.visual{background:linear-gradient(145deg,#242c38,#151b24);border-radius:18px;min-height:190px;padding:26px;color:#fff;position:relative;overflow:hidden}.visual:after{content:"";position:absolute;width:210px;height:210px;border:1px solid #ffffff20;border-radius:50%;right:-45px;top:-80px;box-shadow:0 0 0 26px #ffffff08,0 0 0 54px #ffffff05}.seal{font-size:11px;color:#d7bd8e;letter-spacing:.15em;text-transform:uppercase}.visual strong{display:block;font-size:28px;letter-spacing:-.04em;margin-top:22px}.visual span{display:block;color:#c3cbd6;font-size:12px;margin-top:5px}
.section-label{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#7b8492;margin:8px 0 12px}.requirements{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.req{border:1px solid var(--line);border-radius:12px;padding:11px 13px;display:flex;justify-content:space-between;align-items:center;gap:12px;min-width:0}.req strong{display:block;font-size:12px}.req small{display:block;color:var(--muted);font-size:11px;overflow-wrap:anywhere}.state{font-size:10px;font-weight:800;text-transform:uppercase;white-space:nowrap}.ok{color:var(--ok)}.fail{color:var(--bad)}.warning{color:var(--warn)}.notice{padding:12px 14px;border-radius:12px;background:#fff8eb;border:1px solid #f0dfbf;color:#77551e;font-size:12px;margin:14px 0}.notice.neutral{background:#f3f6fa;border-color:#e1e7ef;color:#596779}.notice.success{background:#eef9f2;border-color:#cfeada;color:#176440}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{min-width:0}.field.full{grid-column:1/-1}.field label{display:block;font-size:12px;font-weight:750;margin:0 0 6px;color:#384252}.field input,.field select{width:100%;border:1px solid #d8dee7;background:white;border-radius:10px;padding:11px 12px;font:inherit;color:var(--ink);min-height:43px;outline:none;transition:border .16s,box-shadow .16s}.field input:focus,.field select:focus{border-color:var(--red2);box-shadow:0 0 0 3px #c52b4d18}.field input::placeholder{color:#9aa3b0}.hint{display:block;color:#7c8797;font-size:11px;margin-top:5px}.radio-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.choice{border:1px solid var(--line);border-radius:13px;padding:15px;display:flex;gap:11px;cursor:pointer;align-items:flex-start}.choice:has(input:checked){border-color:#c52b4d;background:#fff8f9;box-shadow:0 0 0 2px #c52b4d12}.choice input,.checkline input{margin-top:3px;accent-color:var(--red)}.choice strong{display:block;font-size:13px}.choice small{display:block;color:var(--muted);font-size:11px;margin-top:3px}.checkline{display:flex;align-items:flex-start;gap:10px;padding:12px;border:1px solid var(--line);border-radius:12px}.checkline strong{font-size:12px;display:block}.checkline small{font-size:11px;color:var(--muted)}
.actions{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid var(--line)}.actions-right{display:flex;align-items:center;gap:10px}.btn{border:1px solid #dbe1e8;border-radius:10px;padding:10px 16px;background:white;color:#374151;font-weight:750;font-size:12px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;min-height:41px}.btn:hover{background:#f8fafc}.btn.primary{background:var(--red);border-color:var(--red);color:white;box-shadow:0 6px 16px #9f173426}.btn.primary:hover{background:#7f122a}.btn:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
.summary{display:grid;grid-template-columns:1fr 1fr;gap:10px}.summary-item{padding:13px 14px;border:1px solid var(--line);border-radius:12px;min-width:0}.summary-item small{display:block;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}.summary-item strong{display:block;font-size:13px;margin-top:4px;overflow-wrap:anywhere}.progress-list{list-style:none;margin:0;padding:0;display:grid;gap:10px}.progress-item{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px;border:1px solid var(--line);border-radius:12px}.progress-item strong{font-size:12px}.status-chip{background:#f0f2f5;color:#778293;border-radius:20px;padding:5px 9px;font-size:10px;font-weight:800}.status-chip.running{background:#fff5e6;color:#9a6514}.status-chip.done{background:#e9f6ef;color:var(--ok)}.status-chip.error{background:#fff0f1;color:var(--bad)}.msg{display:none;margin-top:16px;padding:12px 14px;border-radius:11px;font-size:12px}.msg.show{display:block}.msg.error{background:#fff1f2;border:1px solid #f2c9cf;color:#9e1b31}.msg.success{background:#eef9f2;border:1px solid #cfeada;color:#176440}.hidden{display:none!important}
@media(max-width:930px){.shell{grid-template-columns:1fr}.sidebar{position:static;padding:15px}.step-list{grid-template-columns:repeat(4,1fr)}.step-nav{padding:8px;justify-content:center}.step-nav span:last-child{display:none}.step-nav .n{width:25px;height:25px;flex-basis:25px}.side-note{margin-top:10px}.panel{min-height:0}}
@media(max-width:620px){.top-inner{padding:15px 16px}.meta{display:none}main{margin:18px auto 36px;padding:0 12px}.step-list{grid-template-columns:repeat(4,1fr)}.step-nav{gap:0}.intro{align-items:flex-start;flex-direction:column;gap:4px}.intro h1{font-size:22px}.panel{padding:20px 16px}.welcome{grid-template-columns:1fr}.welcome h2{font-size:27px}.visual{min-height:140px}.requirements,.grid,.radio-grid,.summary{grid-template-columns:1fr}.field.full{grid-column:auto}.actions{align-items:stretch}.actions-right{margin-left:auto}.btn{padding:10px 12px}.progress-item{padding:12px}}
</style>
</head>
<body>
<header class="top"><div class="top-inner">
    <div class="brand"><div class="mark">GT</div><div><strong>G.TERRAS</strong><small>INOTU · Sistema de Gestão Territorial</small></div></div>
    <div class="meta"><span class="pill">Assistente de instalação</span><span>Versão {{ $versao }}</span></div>
</div></header>

<main><div class="shell">
<aside class="sidebar">
    <h2>Configuração inicial</h2>
    <nav class="step-list" id="step-nav" aria-label="Etapas de instalação">
        <button type="button" class="step-nav active" data-nav="0"><span class="n">01</span><span>Diagnóstico</span></button>
        <button type="button" class="step-nav" data-nav="1" disabled><span class="n">02</span><span>Base de dados</span></button>
        <button type="button" class="step-nav" data-nav="2" disabled><span class="n">03</span><span>Plataforma</span></button>
        <button type="button" class="step-nav" data-nav="3" disabled><span class="n">04</span><span>Modalidade</span></button>
        <button type="button" class="step-nav" data-nav="4" disabled><span class="n">05</span><span>Complementos</span></button>
        <button type="button" class="step-nav" data-nav="5" disabled><span class="n">06</span><span>Administrador</span></button>
        <button type="button" class="step-nav" data-nav="6" disabled><span class="n">07</span><span>Revisão</span></button>
        <button type="button" class="step-nav" data-nav="7" disabled><span class="n">08</span><span>Instalação</span></button>
    </nav>
    <div class="side-note"><strong>Instalação protegida</strong>As credenciais são usadas para validar e configurar a ligação. Não são apresentadas no resumo nem nos logs técnicos.</div>
</aside>

<section class="content">
<div class="intro"><div><div class="eyebrow">INOTU · Configuração do servidor</div><h1>Preparar o G.TERRAS</h1><p>Configure a base de dados, a identidade institucional e o primeiro administrador antes de abrir a plataforma.</p></div><div class="progress-text" id="progress-label">Etapa 1 de 8</div></div>

<form id="install-form" autocomplete="off" novalidate>
<section class="panel active" data-step="0">
    <div class="welcome">
        <div><div class="eyebrow">Bem-vindo ao assistente</div><h2>Uma configuração segura para começar a trabalhar.</h2><p>O assistente verifica os requisitos do servidor, prepara a estrutura da base de dados e configura o primeiro acesso administrativo. As operações são executadas sem apagar dados existentes.</p></div>
        <div class="visual"><div class="seal">Instituto Nacional do Ordenamento do Território e Desenvolvimento Urbano</div><strong>G.TERRAS</strong><span>Gestão territorial · Administração nacional e provincial</span></div>
    </div>
    <div class="section-label">Diagnóstico automático do servidor</div>
    <div class="requirements">
        @foreach ($requisitos as $requisito)
        <div class="req"><span><strong>{{ $requisito['nome'] }}</strong><small>{{ $requisito['detalhe'] }}</small></span><b class="state {{ $requisito['ok'] ? 'ok' : ($requisito['critico'] ? 'fail' : 'warning') }}">{{ $requisito['ok'] ? 'Aprovado' : ($requisito['critico'] ? 'Obrigatório' : 'Aviso') }}</b></div>
        @endforeach
    </div>
    <div class="notice {{ $estadoInstalacao['estado'] === 'never_installed' ? 'neutral' : 'warning' }}"><strong>Estado da base:</strong> {{ $estadoInstalacao['detalhe'] }} O ficheiro de bloqueio local não é considerado prova de instalação concluída.</div>
    @unless ($cumpridos)<div class="notice">Existem requisitos críticos por cumprir. Corrija as permissões ou instale as extensões indicadas e volte a verificar.</div>@endunless
    <div class="actions"><button type="button" class="btn" onclick="window.location.reload()">Voltar a verificar</button><div class="actions-right"><button type="button" class="btn primary" data-next @disabled(! $cumpridos)>Continuar</button></div></div>
</section>

<section class="panel" data-step="1">
    <div class="panel-head"><div><h2>Ligação à base de dados</h2><p>Os valores existentes no ficheiro .env são utilizados como ponto de partida. Teste a ligação antes de avançar.</p></div><span class="step-number">02</span></div>
    <div class="grid">
        <div class="field full"><label for="db_connection">Motor de base de dados</label><select id="db_connection" name="db_connection">
            @if (in_array('sqlite', $drivers, true))<option value="sqlite" @selected($databaseDefaults['db_connection'] === 'sqlite')>SQLite · Ficheiro local</option>@endif
            @if (in_array('mysql', $drivers, true))<option value="mysql" @selected($databaseDefaults['db_connection'] === 'mysql')>MySQL</option><option value="mariadb" @selected($databaseDefaults['db_connection'] === 'mariadb')>MariaDB</option>@endif
            @if ($databaseDefaults['db_connection'] === 'pgsql')<option value="pgsql" disabled selected>PostgreSQL · validação pendente</option>@endif
        </select><small class="hint">Só são apresentados motores cujo driver PDO está instalado.</small></div>
        <div class="field full" data-db-sqlite><label for="db_database_sqlite">Caminho do ficheiro SQLite</label><input id="db_database_sqlite" value="{{ $databaseDefaults['db_connection'] === 'sqlite' ? $databaseDefaults['db_database'] : 'database/database.sqlite' }}" placeholder="database/database.sqlite"></div>
        <div class="field" data-db-remote><label for="db_host">Servidor / Host</label><input id="db_host" name="db_host" value="{{ $databaseDefaults['db_host'] }}" placeholder="127.0.0.1"></div>
        <div class="field" data-db-remote><label for="db_port">Porta</label><input id="db_port" name="db_port" value="{{ $databaseDefaults['db_port'] }}"></div>
        <div class="field full" data-db-remote><label for="db_database_remote">Nome da base de dados</label><input id="db_database_remote" value="{{ $databaseDefaults['db_connection'] === 'sqlite' ? 'gterras' : $databaseDefaults['db_database'] }}" placeholder="gterras"></div>
        <div class="field" data-db-remote><label for="db_username">Utilizador</label><input id="db_username" name="db_username" value="{{ $databaseDefaults['db_username'] }}" autocomplete="off"></div>
        <div class="field" data-db-remote><label for="db_password">Palavra-passe</label><input id="db_password" name="db_password" type="password" autocomplete="new-password" placeholder="Deixe em branco para reutilizar a credencial actual"><small class="hint">Nunca é devolvida ao navegador como valor predefinido.</small></div>
        <div class="field full hidden" data-db-pg><label for="db_maintenance_database">Base de manutenção PostgreSQL</label><input id="db_maintenance_database" name="db_maintenance_database" value="postgres"><small class="hint">Utilizada apenas quando autorizar a criação automática da base de destino.</small></div>
        <div class="field full"><label class="checkline"><input type="checkbox" id="db_create_database" name="db_create_database" value="1"><span><strong>Criar a base de dados automaticamente, se ainda não existir</strong><small>Requer privilégios de criação. Sem esta opção, o instalador apenas testa a base indicada.</small></span></label></div>
    </div>
    <div class="msg" id="db-message" role="status"></div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn" id="test-db">Testar ligação</button><button type="button" class="btn primary" id="next-db" disabled>Continuar</button></div></div>
</section>

<section class="panel" data-step="2">
    <div class="panel-head"><div><h2>Identificação da plataforma</h2><p>Defina os parâmetros regionais e de apresentação desta instalação.</p></div><span class="step-number">03</span></div>
    <div class="grid">
        <div class="field"><label for="app_name">Nome da aplicação</label><input id="app_name" name="app_name" value="{{ $databaseDefaults['app_name'] }}"></div>
        <div class="field"><label for="app_url">URL pública da aplicação</label><input id="app_url" name="app_url" type="url" value="{{ $databaseDefaults['app_url'] }}"></div>
        <div class="field"><label for="app_env">Ambiente</label><select id="app_env" name="app_env"><option value="local" @selected(! $producao)>Desenvolvimento</option><option value="producao" @selected($producao)>Produção</option></select></div>
        <div class="field"><label for="dominio">Domínio principal</label><input id="dominio" name="dominio" value="{{ $databaseDefaults['dominio'] }}" placeholder="gterra.gov.ao"></div>
        <div class="field"><label for="app_locale">Idioma</label><select id="app_locale" name="app_locale"><option value="pt" selected>Português</option></select></div>
        <div class="field"><label for="app_country">País</label><select id="app_country" name="app_country"><option value="AO" selected>Angola</option></select></div>
        <div class="field"><label for="app_timezone">Fuso horário</label><select id="app_timezone" name="app_timezone"><option value="Africa/Luanda" selected>Africa/Luanda</option><option value="UTC">UTC</option></select></div>
    </div>
    <div class="notice neutral">O idioma, país e fuso horário podem ser ajustados posteriormente. O padrão recomendado é Português · Angola · Africa/Luanda.</div><div class="msg" id="platform-message"></div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" data-next>Continuar</button></div></div>
</section>

<section class="panel" data-step="3">
    <div class="panel-head"><div><h2>Modalidade de instalação</h2><p>Escolha o papel institucional que este servidor irá desempenhar no ecossistema G.TERRAS.</p></div><span class="step-number">04</span></div>
    <div class="radio-grid">
        <label class="choice"><input type="radio" name="modo" value="central" @checked($databaseDefaults['modo'] === 'central')><span><strong>Núcleo Central Nacional</strong><small>Administração nacional, gestão central e integração dos nodes provinciais.</small></span></label>
        <label class="choice"><input type="radio" name="modo" value="node" @checked($databaseDefaults['modo'] !== 'central')><span><strong>Node Provincial</strong><small>Instalação operacional vinculada a uma província específica.</small></span></label>
    </div>
    <div class="grid" style="margin-top:18px">
        <div class="field full"><label for="node_code">Província do node</label><select id="node_code" name="node_code"><option value="">Seleccione a província</option>
            @foreach ($provincias as $codigo => $nome)<option value="{{ $codigo }}" @selected($databaseDefaults['node_code'] === $codigo)>{{ $nome }} · {{ $codigo }}</option>@endforeach
        </select><small class="hint">A lista utiliza exclusivamente o catálogo territorial existente no projecto.</small></div>
        <div class="field full" data-node-only><label for="node_name">Nome do node</label><input id="node_name" name="node_name" value="{{ $databaseDefaults['node_name'] }}"></div>
        <div class="field full" data-node-only><label for="central_url">URL do núcleo central</label><input id="central_url" name="central_url" type="url" value="{{ $databaseDefaults['central_url'] }}"></div>
    </div>
    <div class="notice">O catálogo territorial existente identifica as províncias, mas há registos municipais por completar. A instalação não apresentará esses municípios como uma lista nacional definitiva.</div><div class="msg" id="mode-message"></div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" data-next>Continuar</button></div></div>
</section>

<section class="panel" data-step="4">
    <div class="panel-head"><div><h2>Configurações complementares</h2><p>Os serviços opcionais podem ser configurados agora ou posteriormente, sem bloquear a instalação básica.</p></div><span class="step-number">05</span></div>
    <div class="section-label">Email e notificações</div>
    <div class="grid">
        <div class="field"><label for="mail_host">Servidor SMTP</label><input id="mail_host" name="mail_host" value="{{ $databaseDefaults['mail_host'] }}" placeholder="smtp.exemplo.ao"></div>
        <div class="field"><label for="mail_port">Porta SMTP</label><input id="mail_port" name="mail_port" type="number" value="{{ $databaseDefaults['mail_port'] }}" placeholder="587"></div>
        <div class="field"><label for="mail_username">Utilizador SMTP</label><input id="mail_username" name="mail_username" value="{{ $databaseDefaults['mail_username'] }}" autocomplete="off"></div>
        <div class="field"><label for="mail_password">Palavra-passe SMTP</label><input id="mail_password" name="mail_password" type="password" autocomplete="new-password"></div>
        <div class="field"><label for="mail_from_address">Email de envio</label><input id="mail_from_address" name="mail_from_address" type="email" value="{{ $databaseDefaults['mail_from_address'] }}" placeholder="sistema@gterra.gov.ao"></div>
        <div class="field"><label for="mail_from_name">Nome do remetente</label><input id="mail_from_name" name="mail_from_name" value="{{ $databaseDefaults['mail_from_name'] }}" placeholder="G.TERRAS · INOTU"></div>
    </div>
    <div class="notice neutral">Durante a instalação, as sessões e a cache utilizam ficheiros e a fila utiliza a base de dados. Evita-se depender de tabelas ainda não criadas.</div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" data-next>Continuar</button></div></div>
</section>

<section class="panel" data-step="5">
    <div class="panel-head"><div><h2>Administrador inicial</h2><p>Crie a primeira conta de administração. Não são utilizadas contas ou palavras-passe predefinidas.</p></div><span class="step-number">06</span></div>
    <div class="grid">
        <div class="field full"><label for="admin_name">Nome completo</label><input id="admin_name" name="admin_name" autocomplete="name" placeholder="Nome do responsável pela administração"></div>
        <div class="field full"><label for="admin_email">Email institucional</label><input id="admin_email" name="admin_email" type="email" autocomplete="email" placeholder="administrador@instituicao.ao"></div>
        <div class="field"><label for="admin_password">Palavra-passe</label><input id="admin_password" name="admin_password" type="password" autocomplete="new-password"><small class="hint">Mínimo de 10 caracteres em desenvolvimento e 12 em produção.</small></div>
        <div class="field"><label for="admin_password_confirmation">Confirmar palavra-passe</label><input id="admin_password_confirmation" name="admin_password_confirmation" type="password" autocomplete="new-password"></div>
    </div>
    <div class="msg" id="admin-message"></div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" id="next-admin">Continuar</button></div></div>
</section>

<section class="panel" data-step="6">
    <div class="panel-head"><div><h2>Revisão e confirmação</h2><p>Confirme os parâmetros antes de iniciar. As palavras-passe e outros segredos são omitidos deste resumo.</p></div><span class="step-number">07</span></div>
    <div class="summary" id="summary"></div>
    <div class="notice neutral">Serão aplicadas migrations pendentes e carregados apenas dados fundamentais. Não será executado migrate:fresh, db:wipe ou qualquer operação de eliminação.</div>
    <div class="checkline"><input type="checkbox" id="confirm-install"><span><strong>Confirmo que os parâmetros estão correctos e autorizo a preparação desta base de dados.</strong><small>Uma base que contenha utilizadores será protegida contra reinstalação normal.</small></span></div>
    <div class="actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" id="go-install" disabled>Confirmar e avançar</button></div></div>
</section>

<section class="panel" data-step="7">
    <div class="panel-head"><div><h2>Execução da instalação</h2><p>As etapas são executadas no servidor. O estado só será concluído depois de validar a conta, as permissões e o tenant.</p></div><span class="step-number">08</span></div>
    <ul class="progress-list" id="install-progress">
        <li class="progress-item" data-progress="1"><strong>01 · Persistir configuração e validar a ligação</strong><span class="status-chip">Pendente</span></li>
        <li class="progress-item" data-progress="2"><strong>02 · Criar ou actualizar a estrutura (migrations)</strong><span class="status-chip">Pendente</span></li>
        <li class="progress-item" data-progress="3"><strong>03 · Carregar dados fundamentais e configurar tenant</strong><span class="status-chip">Pendente</span></li>
        <li class="progress-item" data-progress="4"><strong>04 · Criar administrador e validar instalação</strong><span class="status-chip">Pendente</span></li>
    </ul>
    <div class="msg" id="install-message"></div>
    <div class="actions" id="install-actions"><button type="button" class="btn" data-prev>Voltar</button><div class="actions-right"><button type="button" class="btn primary" id="install-now">Iniciar instalação</button></div></div>
    <div class="hidden" id="install-complete"><div class="notice success"><strong>Instalação concluída.</strong> O sistema validou a estrutura, os dados fundamentais, o tenant, a conta administrativa e as permissões.</div><div class="actions"><span></span><a class="btn primary" href="/login">Aceder ao login</a></div></div>
</section>
</form>
</section></div></main>

<script>
(() => {
    const $ = (s, root = document) => root.querySelector(s);
    const $$ = (s, root = document) => [...root.querySelectorAll(s)];
    const form = $('#install-form');
    const panels = $$('[data-step]');
    const nav = $$('[data-nav]');
    let current = 0, databaseTested = false, installing = false;

    const mode = () => $('input[name="modo"]:checked')?.value || 'node';
    const driver = () => $('#db_connection').value;
    const databaseName = () => driver() === 'sqlite' ? $('#db_database_sqlite').value.trim() : $('#db_database_remote').value.trim();
    const data = () => {
        const values = Object.fromEntries(new FormData(form).entries());
        values.db_database = databaseName();
        values.db_create_database = $('#db_create_database').checked ? 1 : 0;
        values.db_maintenance_database = $('#db_maintenance_database')?.value || 'postgres';
        values.modo = mode();
        values.node_code = mode() === 'central' ? 'CTR' : $('#node_code').value;
        values.node_name = mode() === 'central' ? 'Núcleo Central Nacional' : $('#node_name').value;
        return values;
    };
    const message = (element, text, success) => {
        element.textContent = text;
        element.className = 'msg show ' + (success ? 'success' : 'error');
    };
    const showDatabaseFields = () => {
        const sqlite = driver() === 'sqlite';
        $$('[data-db-sqlite]').forEach(el => el.classList.toggle('hidden', !sqlite));
        $$('[data-db-remote]').forEach(el => el.classList.toggle('hidden', sqlite));
        $$('[data-db-pg]').forEach(el => el.classList.toggle('hidden', driver() !== 'pgsql'));
        if (!sqlite && !$('#db_port').value) $('#db_port').value = driver() === 'pgsql' ? '5432' : '3306';
    };
    const showModeFields = () => $$('[data-node-only]').forEach(el => el.classList.toggle('hidden', mode() !== 'node'));
    const summary = () => {
        const d = data();
        const rows = [
            ['Motor de dados', d.db_connection.toUpperCase()],['Base de dados', d.db_database],
            ['Aplicação', d.app_name],['URL', d.app_url],['Ambiente', d.app_env === 'producao' ? 'Produção' : 'Desenvolvimento'],
            ['Idioma / país', (d.app_locale || 'pt') + ' / ' + ($('#app_country')?.value || 'AO')],['Fuso horário', d.app_timezone],
            ['Modalidade', d.modo === 'central' ? 'Núcleo Central Nacional' : 'Node Provincial'],
            ['Tenant', d.modo === 'central' ? 'CTR · Núcleo Central Nacional' : d.node_name + ' · ' + d.node_code],
            ['Domínio', d.dominio],['Administrador', d.admin_name + ' · ' + d.admin_email],
            ['Email / SMTP', d.mail_host ? d.mail_host + ':' + (d.mail_port || '') : 'Configuração posterior'],
        ];
        $('#summary').innerHTML = '';
        rows.forEach(([label, value]) => {
            const item = document.createElement('div'); item.className = 'summary-item';
            const small = document.createElement('small'); small.textContent = label;
            const strong = document.createElement('strong'); strong.textContent = value || '—';
            item.append(small, strong); $('#summary').append(item);
        });
    };
    const go = step => {
        current = Math.max(0, Math.min(7, step));
        panels.forEach((panel, index) => panel.classList.toggle('active', index === current));
        nav.forEach((button, index) => {
            button.classList.toggle('active', index === current);
            button.classList.toggle('done', index < current);
            button.disabled = index > current;
        });
        $('#progress-label').textContent = 'Etapa ' + (current + 1) + ' de 8';
        if (current === 1) showDatabaseFields();
        if (current === 3) showModeFields();
        if (current === 6) summary();
        window.scrollTo({top:0,behavior:'smooth'});
    };
    const validateAdmin = () => {
        const d = data(), box = $('#admin-message');
        if (!d.admin_name || d.admin_name.trim().length < 3) { message(box,'Indique o nome completo do administrador.',false); return false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.admin_email || '')) { message(box,'Indique um email institucional válido.',false); return false; }
        const min = d.app_env === 'producao' ? 12 : 10;
        if ((d.admin_password || '').length < min) { message(box,'A palavra-passe deve ter pelo menos ' + min + ' caracteres.',false); return false; }
        if (d.admin_password !== d.admin_password_confirmation) { message(box,'A confirmação da palavra-passe não coincide.',false); return false; }
        box.className = 'msg'; return true;
    };
    const token = async () => {
        const response = await fetch('/instalar/token',{headers:{Accept:'application/json'},credentials:'same-origin'});
        if (!response.ok) throw new Error('Não foi possível validar a sessão do instalador. Actualize a página.');
        return (await response.json()).token;
    };
    const send = async (url, body) => {
        const response = await fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':await token()},body:JSON.stringify(body)});
        const result = await response.json().catch(()=>({}));
        if (!response.ok || result.ok === false) {
            const errors = result.errors ? Object.values(result.errors).flat().join(' ') : '';
            throw new Error(errors || result.mensagem || result.message || 'O servidor devolveu o erro ' + response.status + '.');
        }
        return result;
    };

    $('#db_connection').addEventListener('change',()=>{
        databaseTested=false;$('#next-db').disabled=true;$('#db-message').className='msg';showDatabaseFields();
    });
    $$('[data-db-remote] input,[data-db-sqlite] input,#db_create_database,#db_maintenance_database').forEach(el=>{
        el.addEventListener('input',()=>{databaseTested=false;$('#next-db').disabled=true;});
        el.addEventListener('change',()=>{databaseTested=false;$('#next-db').disabled=true;});
    });
    $('#test-db').addEventListener('click',async event=>{
        const button=event.currentTarget;button.disabled=true;
        try{const result=await send('/instalar/testar-base',data());databaseTested=true;$('#next-db').disabled=false;message($('#db-message'),result.mensagem,true);}
        catch(error){databaseTested=false;$('#next-db').disabled=true;message($('#db-message'),error.message,false);}
        finally{button.disabled=false;}
    });
    $('#next-db').addEventListener('click',()=>{if(databaseTested)go(2);});
    $('#next-admin').addEventListener('click',()=>{if(validateAdmin())go(6);});
    $('#go-install').addEventListener('click',()=>{if($('#confirm-install').checked)go(7);});
    $('#confirm-install').addEventListener('change',event=>{$('#go-install').disabled=!event.target.checked;});
    $$('[data-next]').forEach(button=>button.addEventListener('click',()=>{
        if(current===0&&button.disabled)return;
        if(current===2){
            const d=data();
            if(!d.app_name||d.app_name.trim().length<2){message($('#platform-message'),'Indique o nome da aplicação.',false);return;}
            try{new URL(d.app_url);}catch{message($('#platform-message'),'Indique um URL público válido, incluindo https://.',false);return;}
            if(!/^[A-Za-z0-9.-]+$/.test(d.dominio||'')){message($('#platform-message'),'Indique um domínio válido.',false);return;}
        }
        if(current===3&&mode()==='node'&&!$('#node_code').value){message($('#mode-message'),'Seleccione uma província para este node.',false);return;}
        go(current+1);
    }));
    $$('[data-prev]').forEach(button=>button.addEventListener('click',()=>go(current-1)));
    nav.forEach(button=>button.addEventListener('click',()=>{const target=Number(button.dataset.nav);if(target<=current&&!installing)go(target);}));
    $$('input[name="modo"]').forEach(input=>input.addEventListener('change',showModeFields));
    $('#node_code').addEventListener('change',()=>{
        const selected=$('#node_code').selectedOptions[0];if(selected?.value)$('#node_name').value=selected.textContent.split(' · ')[0];
    });
    const setProgress=(step,status,text)=>{
        const chip=$('[data-progress="' + step + '"] .status-chip');chip.textContent=text;chip.className='status-chip ' + status;
    };
    $('#install-now').addEventListener('click',async()=>{
        if(installing)return;installing=true;const button=$('#install-now');button.disabled=true;$('#install-message').className='msg';
        const payload=data();
        let retryFrom = Number(button.dataset.retryFrom || 1);
        for(const step of [1,2,3,4].filter(value => value >= retryFrom)){
            setProgress(step,'running','Em execução');
            try{await send('/instalar/passo/' + step,payload);setProgress(step,'done','Concluída');retryFrom = step + 1;}
            catch(error){setProgress(step,'error','Falhou');message($('#install-message'),error.message,false);button.textContent='Tentar novamente';button.disabled=false;installing=false;button.dataset.retryFrom = String(step);return;}
        }
        $('#install-message').className='msg';$('#install-actions').classList.add('hidden');$('#install-complete').classList.remove('hidden');nav.forEach(button=>button.classList.add('done'));installing=false;
    });
    showDatabaseFields();showModeFields();go(0);
})();
</script>
</body>
</html>
