<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Instalação — GTerra</title>
    <style>
        :root { --vermelho:#c8102e; --preto:#18181b; --cinza:#71717a; --borda:#e4e4e7; --fundo:#f4f4f5; --ok:#15803d; --erro:#b91c1c; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; background:var(--fundo); color:var(--preto); }
        header { background:var(--preto); color:#fff; padding:20px 24px; border-bottom:4px solid var(--vermelho); }
        header h1 { margin:0; font-size:20px; } header p { margin:4px 0 0; color:#a1a1aa; font-size:13px; }
        main { max-width:760px; margin:24px auto; padding:0 16px 48px; }
        .passos { display:flex; gap:6px; margin-bottom:16px; }
        .passos span { flex:1; text-align:center; font-size:12px; padding:8px 4px; border-radius:8px; background:#e4e4e7; color:var(--cinza); }
        .passos span.activo { background:var(--vermelho); color:#fff; font-weight:600; }
        .passos span.feito { background:#dcfce7; color:var(--ok); }
        .cartao { background:#fff; border:1px solid var(--borda); border-radius:12px; padding:24px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .cartao h2 { margin:0 0 4px; font-size:18px; } .cartao > p.sub { margin:0 0 18px; color:var(--cinza); font-size:14px; }
        .grelha { display:grid; grid-template-columns:1fr 1fr; gap:14px; } .grelha .larga { grid-column:1/-1; }
        label { display:block; font-size:13px; font-weight:600; margin-bottom:5px; }
        input, select { width:100%; padding:9px 11px; border:1px solid #d4d4d8; border-radius:8px; font-size:14px; background:#fff; }
        input:focus, select:focus { outline:2px solid #fca5a5; border-color:var(--vermelho); }
        small { color:var(--cinza); font-size:12px; }
        .botoes { display:flex; justify-content:space-between; gap:8px; margin-top:22px; }
        button { border:0; border-radius:8px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer; }
        .primario { background:var(--vermelho); color:#fff; } .secundario { background:#e4e4e7; color:var(--preto); }
        button:disabled { opacity:.5; cursor:not-allowed; }
        ul.lista { list-style:none; margin:0; padding:0; } ul.lista li { display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px solid var(--borda); font-size:14px; }
        .ok { color:var(--ok); } .falha { color:var(--erro); } .aviso { background:#fef3c7; color:#92400e; padding:10px 12px; border-radius:8px; font-size:13px; margin-top:14px; }
        .msg { margin-top:12px; font-size:13px; padding:10px 12px; border-radius:8px; display:none; }
        .msg.ok { background:#dcfce7; display:block; } .msg.falha { background:#fee2e2; display:block; }
        .oculto { display:none !important; }
        @media (max-width:560px){ .grelha{grid-template-columns:1fr} .passos span{font-size:10px} }
    </style>
</head>
<body>
<header>
    <h1>GTerra · INOTU</h1>
    <p>Assistente de instalação e configuração</p>
</header>

<main>
    <div class="passos" id="passos">
        <span>1. Requisitos</span><span>2. Base de dados</span><span>3. Sistema</span><span>4. Administrador</span><span>5. Instalar</span>
    </div>

    <form id="form" autocomplete="off" onsubmit="return false">
        {{-- 1. Requisitos --}}
        <section class="cartao" data-passo="0">
            <h2>Requisitos do servidor</h2>
            <p class="sub">Verificação do PHP, extensões e permissões de escrita.</p>
            <ul class="lista">
                @foreach ($requisitos as $r)
                    <li><span>{{ $r['nome'] }} <small>{{ $r['detalhe'] }}</small></span>
                        <strong class="{{ $r['ok'] ? 'ok' : 'falha' }}">{{ $r['ok'] ? '✓' : ($r['critico'] ? '✗' : '! opcional') }}</strong></li>
                @endforeach
            </ul>
            @unless ($cumpridos)
                <div class="aviso">Existem requisitos obrigatórios por cumprir. Corrija-os e actualize a página.</div>
            @endunless
            <div class="botoes">
                <button type="button" class="secundario" onclick="location.reload()">Verificar novamente</button>
                <button type="button" class="primario" data-seguinte @disabled(! $cumpridos)>Continuar</button>
            </div>
        </section>

        {{-- 2. Base de dados --}}
        <section class="cartao oculto" data-passo="1">
            <h2>Base de dados</h2>
            <p class="sub">A ligação é testada antes de avançar. Em MySQL, a base é criada se não existir.</p>
            <div class="grelha">
                <div class="larga"><label for="db_connection">Tipo</label>
                    <select id="db_connection" name="db_connection">
                        @if ($sqlite)<option value="sqlite">SQLite (ficheiro local)</option>@endif
                        <option value="mysql" @selected(! $sqlite)>MySQL</option>
                        <option value="mariadb">MariaDB</option>
                        <option value="pgsql">PostgreSQL</option>
                    </select></div>
                <div class="larga" data-so="sqlite"><label for="db_sqlite">Ficheiro SQLite</label>
                    <input id="db_sqlite" data-campo="sqlite" value="{{ $caminhoSqlite }}"></div>
                <div data-so="rede"><label for="db_host">Servidor</label><input id="db_host" name="db_host" value="127.0.0.1"></div>
                <div data-so="rede"><label for="db_port">Porta</label><input id="db_port" name="db_port" value="3306"></div>
                <div class="larga" data-so="rede"><label for="db_nome">Nome da base de dados</label><input id="db_nome" data-campo="rede" value="gterra"></div>
                <div data-so="rede"><label for="db_username">Utilizador</label><input id="db_username" name="db_username" value="root"></div>
                <div data-so="rede"><label for="db_password">Palavra-passe</label><input id="db_password" name="db_password" type="password"></div>
            </div>
            <div class="msg" id="msg-base"></div>
            <div class="botoes">
                <button type="button" class="secundario" data-anterior>Voltar</button>
                <span><button type="button" class="secundario" id="testar">Testar ligação</button>
                <button type="button" class="primario" data-seguinte disabled id="seg-base">Continuar</button></span>
            </div>
        </section>

        {{-- 3. Sistema --}}
        <section class="cartao oculto" data-passo="2">
            <h2>Configuração do sistema</h2>
            <p class="sub">Identificação da aplicação e papel desta instalação.</p>
            <div class="grelha">
                <div><label for="app_name">Nome da aplicação</label><input id="app_name" name="app_name" value="GTerra"></div>
                <div><label for="app_url">URL da aplicação</label><input id="app_url" name="app_url" value="{{ $urlActual }}"></div>
                <div><label for="app_env">Ambiente</label>
                    <select id="app_env" name="app_env">
                        <option value="producao" @selected($producao)>Produção</option>
                        <option value="local" @selected(! $producao)>Desenvolvimento</option>
                    </select></div>
                <div><label for="dominio">Domínio principal</label><input id="dominio" name="dominio" value="gterra.gov.ao"></div>
                <div class="larga"><label for="modo">Papel desta instalação</label>
                    <select id="modo" name="modo">
                        <option value="node">Node provincial</option>
                        <option value="central">Núcleo central nacional</option>
                    </select></div>
                <div data-so-modo="node"><label for="node_code">Código do node (3 letras)</label><input id="node_code" name="node_code" value="HUI" maxlength="3"></div>
                <div data-so-modo="node"><label for="node_name">Nome do node</label><input id="node_name" name="node_name" value="Província da Huíla"></div>
                <div class="larga" data-so-modo="node"><label for="central_url">URL do núcleo central</label><input id="central_url" name="central_url" value="https://gterra.gov.ao"></div>
            </div>
            <div class="botoes">
                <button type="button" class="secundario" data-anterior>Voltar</button>
                <button type="button" class="primario" data-seguinte>Continuar</button>
            </div>
        </section>

        {{-- 4. Administrador --}}
        <section class="cartao oculto" data-passo="3">
            <h2>Super administrador</h2>
            <p class="sub">Conta com acesso total ao sistema.</p>
            <div class="grelha">
                <div class="larga"><label for="admin_name">Nome completo</label><input id="admin_name" name="admin_name"></div>
                <div class="larga"><label for="admin_email">Email</label><input id="admin_email" name="admin_email" type="email"></div>
                <div><label for="admin_password">Palavra-passe (mín. 8)</label><input id="admin_password" name="admin_password" type="password"></div>
                <div><label for="admin_password_confirmation">Confirmar palavra-passe</label><input id="admin_password_confirmation" name="admin_password_confirmation" type="password"></div>
                @unless ($producao)
                    <div class="larga"><label style="font-weight:400"><input type="checkbox" id="demo" style="width:auto"> Carregar utilizadores de demonstração (palavras-passe conhecidas; só para desenvolvimento)</label></div>
                @endunless
            </div>
            <div class="msg" id="msg-admin"></div>
            <div class="botoes">
                <button type="button" class="secundario" data-anterior>Voltar</button>
                <button type="button" class="primario" id="validar-admin">Continuar</button>
            </div>
        </section>

        {{-- 5. Instalar --}}
        <section class="cartao oculto" data-passo="4">
            <h2>Instalar</h2>
            <p class="sub" id="sub-instalar">Reveja e confirme. A instalação cria a estrutura da base de dados e os dados base.</p>
            <ul class="lista" id="progresso">
                <li data-p="1"><span>Gravar configuração (.env)</span><strong>—</strong></li>
                <li data-p="2"><span>Criar estrutura da base de dados</span><strong>—</strong></li>
                <li data-p="3"><span>Carregar dados base</span><strong>—</strong></li>
                <li data-p="4"><span>Criar super administrador e finalizar</span><strong>—</strong></li>
            </ul>
            <div class="msg" id="msg-instalar"></div>
            <div class="botoes" id="botoes-instalar">
                <button type="button" class="secundario" data-anterior>Voltar</button>
                <button type="button" class="primario" id="instalar">Instalar agora</button>
            </div>
            <div class="oculto" id="concluido">
                <div class="aviso" style="background:#dcfce7;color:#166534">Instalação concluída. Se os estilos não carregarem, execute <code>npm install &amp;&amp; npm run build</code> no servidor.</div>
                <div class="botoes"><span></span><a class="primario" style="text-decoration:none;padding:10px 18px;border-radius:8px;color:#fff" href="/login">Ir para o login</a></div>
            </div>
        </section>
    </form>
</main>

<script>
(() => {
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const secoes = $$('[data-passo]');
    const indicadores = $$('#passos span');
    let actual = 0;

    const dados = () => {
        const d = Object.fromEntries(new FormData($('#form')).entries());
        d.db_database = d.db_connection === 'sqlite' ? $('#db_sqlite').value : $('#db_nome').value;
        d.demo = $('#demo')?.checked ? 1 : 0;
        return d;
    };

    const ir = (n) => {
        actual = n;
        secoes.forEach((s, i) => s.classList.toggle('oculto', i !== n));
        indicadores.forEach((s, i) => { s.className = i === n ? 'activo' : (i < n ? 'feito' : ''); });
        if (n === 1) actualizarBase();
        if (n === 2) actualizarModo();
        window.scrollTo(0, 0);
    };

    const mostrar = (el, texto, ok) => { el.textContent = texto; el.className = 'msg ' + (ok ? 'ok' : 'falha'); };

    const actualizarBase = () => {
        const sqlite = $('#db_connection').value === 'sqlite';
        $$('[data-so="sqlite"]').forEach(e => e.classList.toggle('oculto', !sqlite));
        $$('[data-so="rede"]').forEach(e => e.classList.toggle('oculto', sqlite));
    };
    const actualizarModo = () => $$('[data-so-modo]').forEach(e => e.classList.toggle('oculto', e.dataset.soModo !== $('#modo').value));

    const invalidarTeste = () => { $('#seg-base').disabled = true; };
    $('#db_connection').addEventListener('change', () => {
        actualizarBase(); invalidarTeste();
        if ($('#db_connection').value !== 'sqlite') $('#db_port').value = $('#db_connection').value === 'pgsql' ? '5432' : '3306';
    });
    $$('[data-so] input').forEach(i => i.addEventListener('input', invalidarTeste));
    $('#modo').addEventListener('change', actualizarModo);

    $$('[data-seguinte]').forEach(b => b.addEventListener('click', () => ir(actual + 1)));
    $$('[data-anterior]').forEach(b => b.addEventListener('click', () => ir(actual - 1)));

    // O nome do cookie de sessão depende de APP_NAME; pede-se novo token após alterar o .env.
    const token = async () => (await (await fetch('/instalar/token', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })).json()).token;

    const enviar = async (url, corpo) => {
        const r = await fetch(url, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': await token() },
            body: JSON.stringify(corpo),
        });
        const j = await r.json().catch(() => ({}));
        if (!r.ok || j.ok === false) {
            const erros = j.errors ? Object.values(j.errors).flat().join(' ') : '';
            throw new Error(erros || j.mensagem || j.message || 'Erro inesperado (' + r.status + ').');
        }
        return j;
    };

    $('#testar').addEventListener('click', async (e) => {
        e.target.disabled = true;
        try {
            const j = await enviar('/instalar/testar-base', dados());
            mostrar($('#msg-base'), j.mensagem, true);
            $('#seg-base').disabled = false;
        } catch (err) { mostrar($('#msg-base'), err.message, false); invalidarTeste(); }
        e.target.disabled = false;
    });

    $('#validar-admin').addEventListener('click', () => {
        const d = dados();
        if (!d.admin_name || !d.admin_email.includes('@')) return mostrar($('#msg-admin'), 'Indique o nome e um email válido.', false);
        if (d.admin_password.length < 8) return mostrar($('#msg-admin'), 'A palavra-passe deve ter pelo menos 8 caracteres.', false);
        if (d.admin_password !== d.admin_password_confirmation) return mostrar($('#msg-admin'), 'A confirmação não coincide.', false);
        $('#msg-admin').className = 'msg';
        ir(4);
    });

    $('#instalar').addEventListener('click', async () => {
        const d = dados();
        $('#botoes-instalar').classList.add('oculto');
        $('#msg-instalar').className = 'msg';
        for (const p of [1, 2, 3, 4]) {
            const li = $(`#progresso [data-p="${p}"] strong`);
            li.textContent = '⏳';
            try {
                await enviar('/instalar/passo/' + p, d);
                li.textContent = '✓'; li.className = 'ok';
            } catch (err) {
                li.textContent = '✗'; li.className = 'falha';
                mostrar($('#msg-instalar'), err.message, false);
                $('#botoes-instalar').classList.remove('oculto');
                $('#instalar').textContent = 'Tentar novamente';
                return;
            }
        }
        $('#sub-instalar').textContent = 'Tudo pronto.';
        $('#concluido').classList.remove('oculto');
        indicadores.forEach(s => s.className = 'feito');
    });

    ir(0);
})();
</script>
</body>
</html>
