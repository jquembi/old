<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>G.TERRAS — Sistema de Gestão Territorial</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .gov-stripe { height: 4px; background: linear-gradient(90deg, #C30E20 0%, #C30E20 50%, #000 50%, #000 100%); }
            .home-header a.nav-link { color: rgb(63 63 70); }
            .home-header a.nav-link:hover { color: #C30E20; }

            .hero { position: relative; min-height: min(100svh, 880px); background: #07080d; overflow: hidden; }
            .hero-slide {
                position: absolute; inset: 0;
                display: flex; align-items: center;
                opacity: 0; visibility: hidden;
                transition: opacity .7s ease, visibility .7s ease;
            }
            .hero-slide.is-active { opacity: 1; visibility: visible; z-index: 1; }
            .hero-bg { position: absolute; inset: 0; }
            .hero-bg::after {
                content: "";
                position: absolute; inset: 0;
                background:
                    radial-gradient(700px 420px at 85% 20%, rgba(242, 203, 19, .16), transparent 60%),
                    linear-gradient(180deg, rgba(0,0,0,.15), rgba(0,0,0,.45));
            }
            .hero-grid {
                position: relative; z-index: 1;
                width: 100%; max-width: 80rem;
                margin: 0 auto;
                padding: 7.5rem 1.25rem 7rem;
                display: grid; gap: 2.5rem; align-items: center;
            }
            @media (min-width: 1024px) {
                .hero-grid { grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr); padding: 8rem 2rem 8rem; }
            }
            .hero-kicker {
                display: inline-flex; align-items: center; gap: .5rem;
                padding: .35rem .8rem; border-radius: 999px;
                border: 1px solid rgba(255,255,255,.2);
                background: rgba(255,255,255,.08);
                color: #F2CB13; font-size: .72rem; font-weight: 700;
                letter-spacing: .14em; text-transform: uppercase;
                backdrop-filter: blur(10px);
            }
            .hero-title { margin: 1rem 0 .75rem; color: white; font-size: clamp(2rem, 4.4vw, 3.6rem); line-height: 1.08; font-weight: 700; letter-spacing: -.02em; max-width: 16ch; }
            .hero-title span { color: #F2CB13; }
            .hero-text { max-width: 38rem; color: rgba(255,255,255,.78); font-size: 1.05rem; line-height: 1.65; }
            .hero-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.75rem; }
            .hero-btn {
                display: inline-flex; align-items: center; gap: .5rem;
                padding: .85rem 1.35rem; border-radius: .9rem;
                font-size: .95rem; font-weight: 700; text-decoration: none;
                transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
            }
            .hero-btn-primary { background: #C30E20; color: white; box-shadow: 0 12px 30px -12px rgba(195,14,32,.8); }
            .hero-btn-primary:hover { background: #a00c1a; transform: translateY(-1px); }
            .hero-btn-ghost { background: rgba(255,255,255,.1); color: white; border: 1px solid rgba(255,255,255,.22); backdrop-filter: blur(10px); }
            .hero-btn-ghost:hover { background: rgba(255,255,255,.18); }
            .hero-panel {
                border-radius: 1.6rem;
                border: 1px solid rgba(255,255,255,.18);
                background: rgba(255,255,255,.08);
                box-shadow: 0 30px 70px -30px rgba(0,0,0,.65), inset 0 1px 0 rgba(255,255,255,.25);
                backdrop-filter: blur(18px) saturate(1.3);
                padding: 1.25rem;
            }
            .hero-logo-plate {
                display: flex; align-items: center; justify-content: center;
                min-height: 180px; border-radius: 1.1rem;
                background: rgba(255,255,255,.92);
                padding: 1.5rem;
            }
            .hero-logo-plate img { max-height: 140px; width: auto; max-width: 100%; object-fit: contain; }
            .hero-facts { display: grid; gap: .65rem; margin-top: .9rem; }
            .hero-fact {
                display: flex; gap: .75rem; align-items: center;
                padding: .7rem .8rem; border-radius: .85rem;
                background: rgba(0,0,0,.22); border: 1px solid rgba(255,255,255,.08);
                color: white;
            }
            .hero-fact b { display: block; font-size: .85rem; }
            .hero-fact span { display: block; font-size: .75rem; color: rgba(255,255,255,.62); }
            .hero-controls {
                position: absolute; z-index: 3; left: 0; right: 0; bottom: 1.25rem;
                display: flex; align-items: center; justify-content: space-between;
                max-width: 80rem; margin: 0 auto; padding: 0 1.25rem;
                pointer-events: none;
            }
            .hero-controls > * { pointer-events: auto; }
            .hero-nav { display: flex; gap: .5rem; }
            .hero-arrow, .hero-dot {
                border: 1px solid rgba(255,255,255,.25);
                background: rgba(255,255,255,.1);
                color: white; backdrop-filter: blur(10px); cursor: pointer;
            }
            .hero-arrow {
                width: 42px; height: 42px; border-radius: 999px;
                display: grid; place-items: center;
            }
            .hero-arrow:hover { background: rgba(255,255,255,.22); }
            .hero-dots { display: flex; align-items: center; gap: .45rem; }
            .hero-dot { width: 8px; height: 8px; border-radius: 999px; padding: 0; position: relative; overflow: hidden; }
            .hero-dot.is-active { width: 2.4rem; background: rgba(255,255,255,.18); }
            .hero-dot.is-active::after {
                content: ""; position: absolute; inset: 0; background: #C30E20;
                transform-origin: left center; transform: scaleX(0);
                animation: hero-fill var(--hero-ms, 7s) linear forwards;
            }
            .hero.is-paused .hero-dot.is-active::after { animation-play-state: paused; }
            @keyframes hero-fill { to { transform: scaleX(1); } }
            .hero-count { color: rgba(255,255,255,.7); font-size: .75rem; letter-spacing: .14em; font-variant-numeric: tabular-nums; }
            .hero-count b { color: white; }
            @media (max-width: 1023px) {
                .hero { min-height: auto; overflow: visible; }
                .hero-slide { position: relative; display: none; inset: auto; }
                .hero-slide.is-active { display: flex; }
                .hero-controls { position: relative; bottom: auto; margin: -2.5rem auto 1.5rem; }
                .hero-logo-plate { min-height: 120px; }
                .hero-logo-plate img { max-height: 96px; }
            }
            @media (prefers-reduced-motion: reduce) {
                .hero-slide { transition: none; }
                .hero-dot.is-active::after { animation: none; transform: scaleX(1); }
            }
            .glass-card {
                border-radius: 1.25rem;
                border: 1px solid rgba(255,255,255,.7);
                background: rgba(255,255,255,.62);
                backdrop-filter: blur(16px) saturate(1.3);
                box-shadow: 0 18px 40px -24px rgba(15,23,42,.4), inset 0 1px 0 rgba(255,255,255,.8);
            }
            .glass-card:hover { transform: translateY(-3px); }
        </style>
    </head>
    <body class="theme-glass text-neutral-900 antialiased font-sans">
        <a href="#conteudo" class="skip-link">Ir para o conteúdo</a>
        <div class="gov-stripe"></div>

        <header class="home-header sticky top-0 z-50 border-b border-white/50 bg-white/70 shadow-[0_10px_30px_-18px_rgba(15,23,42,.35)] backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-10 w-auto" />
                    <span class="hidden h-8 w-px bg-neutral-300 sm:block"></span>
                    <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-10 w-auto" />
                    <span class="hidden flex-col leading-tight md:flex">
                        <span class="text-sm font-semibold text-neutral-900">G.TERRAS</span>
                        <span class="text-[11px] text-neutral-500">Gestão territorial da Huíla</span>
                    </span>
                </a>
                <nav class="flex items-center gap-1 sm:gap-2">
                    <a href="#plataforma" class="nav-link hidden px-3 py-2 text-sm font-semibold lg:inline-flex">Plataforma</a>
                    <a href="#modulos" class="nav-link hidden px-3 py-2 text-sm font-semibold lg:inline-flex">Módulos</a>
                    <a href="{{ route('portal.home') }}" class="nav-link hidden px-3 py-2 text-sm font-semibold sm:inline-flex">Portal do Utente</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-[#C30E20] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a00c1a]">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg bg-[#C30E20] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a00c1a]">Iniciar sessão</a>
                    @endauth
                </nav>
            </div>
        </header>

        <section class="hero" id="hero" tabindex="0" aria-roledescription="apresentação" aria-label="Apresentação do G.TERRAS">
            <article class="hero-slide is-active" aria-hidden="false">
                <div class="hero-bg" style="background: radial-gradient(900px 500px at 0% 100%, rgba(195,14,32,.45), transparent 55%), linear-gradient(145deg, #0a0a0a 0%, #1a0008 55%, #2a0a12 100%);"></div>
                <div class="hero-grid">
                    <div>
                        <p class="hero-kicker">INOTU · Província da Huíla</p>
                        <h1 class="hero-title">A base de dados provincial de <span>gestão de terras</span></h1>
                        <p class="hero-text">Plataforma do Instituto Nacional de Ordenamento do Território e Urbanismo para processos fundiários, loteamentos e operações urbanísticas.</p>
                        <div class="hero-actions">
                            @auth
                                <a href="{{ route('dashboard') }}" class="hero-btn hero-btn-primary">Aceder ao painel</a>
                            @else
                                <a href="{{ route('login') }}" class="hero-btn hero-btn-primary">Iniciar sessão</a>
                            @endauth
                            <a href="{{ route('portal.home') }}" class="hero-btn hero-btn-ghost">Portal do utente</a>
                        </div>
                        <div id="desktopBanner" class="mt-5 hidden flex-wrap gap-2">
                            <div id="bannerInstalado" class="hidden">
                                <a href="gterras://open" class="hero-btn hero-btn-ghost">Abrir no G.TERRAS Desktop</a>
                            </div>
                            <div id="bannerTransferir" class="hidden">
                                <a href="{{ route('desktop.download') }}" class="hero-btn hero-btn-ghost">
                                    <span id="bannerTexto">Transferir G.TERRAS Desktop</span>
                                    <span id="bannerOS" class="text-xs font-medium text-white/60"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <aside class="hero-panel">
                        <div class="hero-logo-plate">
                            <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" />
                        </div>
                        <div class="hero-facts">
                            <div class="hero-fact"><div><b>República de Angola</b><span>Enquadramento institucional</span></div></div>
                            <div class="hero-fact"><div><b>22 municípios</b><span>Cobertura da Província da Huíla</span></div></div>
                        </div>
                    </aside>
                </div>
            </article>

            <article class="hero-slide" aria-hidden="true">
                <div class="hero-bg" style="background: radial-gradient(800px 480px at 100% 0%, rgba(195,14,32,.35), transparent 50%), linear-gradient(160deg, #0c1018 0%, #1a0a12 100%);"></div>
                <div class="hero-grid">
                    <div>
                        <p class="hero-kicker">Fluxo fundiário</p>
                        <h2 class="hero-title">Do registo ao parecer, com <span>rastreio em cada etapa</span></h2>
                        <p class="hero-text">Cada processo fica associado a quem o criou, quem o analisou e quando mudou de estado. A auditoria deixa de ser um relatório à parte.</p>
                        <div class="hero-actions">
                            <a href="#plataforma" class="hero-btn hero-btn-primary">Ver a plataforma</a>
                            <a href="#acesso" class="hero-btn hero-btn-ghost">Como aceder</a>
                        </div>
                    </div>
                    <aside class="hero-panel">
                        <div class="hero-logo-plate">
                            <img src="/imagens/no-background/INOTU.png" alt="INOTU" />
                        </div>
                        <div class="hero-facts">
                            <div class="hero-fact"><div><b>Pareceres e pagamentos</b><span>RUPE, referências e estado do processo</span></div></div>
                            <div class="hero-fact"><div><b>Permissões por nível</b><span>INOTU, provincial, municipal e técnico</span></div></div>
                        </div>
                    </aside>
                </div>
            </article>

            <article class="hero-slide" aria-hidden="true">
                <div class="hero-bg" style="background: radial-gradient(760px 420px at 10% 80%, rgba(242,203,19,.2), transparent 55%), linear-gradient(150deg, #120806 0%, #3a140c 100%);"></div>
                <div class="hero-grid">
                    <div>
                        <p class="hero-kicker">Território</p>
                        <h2 class="hero-title">Municípios, loteamentos e o <span>mapa no mesmo sítio</span></h2>
                        <p class="hero-text">A leitura territorial organiza província e município, com editor geoespacial para parcelas e exportação em GeoJSON, KML, DXF e CSV.</p>
                        <div class="hero-actions">
                            <a href="#modulos" class="hero-btn hero-btn-primary">Ver módulos</a>
                            @guest
                                <a href="{{ route('login') }}" class="hero-btn hero-btn-ghost">Entrar na área institucional</a>
                            @endguest
                        </div>
                    </div>
                    <aside class="hero-panel">
                        <div class="hero-logo-plate">
                            <img src="/imagens/no-background/MINOPUH.png" alt="MINOPUH" />
                        </div>
                        <div class="hero-facts">
                            <div class="hero-fact"><div><b>MINOPUH</b><span>Obras públicas, urbanismo e habitação</span></div></div>
                            <div class="hero-fact"><div><b>Instrumentos urbanísticos</b><span>Ordenamento, cartas e operações</span></div></div>
                        </div>
                    </aside>
                </div>
            </article>

            <article class="hero-slide" aria-hidden="true">
                <div class="hero-bg" style="background: radial-gradient(680px 380px at 80% 90%, rgba(195,14,32,.32), transparent 55%), linear-gradient(155deg, #07110e 0%, #1a0a10 100%);"></div>
                <div class="hero-grid">
                    <div>
                        <p class="hero-kicker">Projecto SONA</p>
                        <h2 class="hero-title">Requalificação urbana ligada à <span>gestão fundiária</span></h2>
                        <p class="hero-text">O G.TERRAS integra o acompanhamento dos projectos de requalificação e reconversão urbana da Província da Huíla no mesmo sistema de terras.</p>
                        <div class="hero-actions">
                            <a href="#modulos" class="hero-btn hero-btn-primary">Módulos do sistema</a>
                            <a href="{{ route('portal.home') }}" class="hero-btn hero-btn-ghost">Consultar um processo</a>
                        </div>
                    </div>
                    <aside class="hero-panel">
                        <div class="hero-logo-plate">
                            <img src="/imagens/no-background/SONA.png" alt="Projecto SONA" />
                        </div>
                        <div class="hero-facts">
                            <div class="hero-fact"><div><b>Projecto SONA</b><span>Requalificação e reconversão urbana</span></div></div>
                            <div class="hero-fact"><div><b>Uma só base</b><span>Terras, pagamentos e auditoria</span></div></div>
                        </div>
                    </aside>
                </div>
            </article>

            <div class="hero-controls">
                <div class="hero-nav">
                    <button type="button" class="hero-arrow" data-hero="prev" aria-label="Slide anterior">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" class="hero-arrow" data-hero="next" aria-label="Slide seguinte">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hero-dots" id="heroDots">
                        <button type="button" class="hero-dot is-active" data-hero-go="0" aria-label="Gestão de terras" aria-current="true"></button>
                        <button type="button" class="hero-dot" data-hero-go="1" aria-label="Processos fundiários"></button>
                        <button type="button" class="hero-dot" data-hero-go="2" aria-label="Território"></button>
                        <button type="button" class="hero-dot" data-hero-go="3" aria-label="Projecto SONA"></button>
                    </div>
                    <p class="hero-count"><b id="heroIndex">01</b> / 04</p>
                </div>
            </div>
        </section>

        <section class="relative z-10 -mt-8 px-4 sm:px-6 lg:px-8" aria-label="Instituições">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-3 rounded-2xl border border-white/70 bg-white/75 px-4 py-4 shadow-lg backdrop-blur-xl sm:gap-6 sm:px-8">
                <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-12 w-auto" />
                <img src="/imagens/no-background/MINOPUH.png" alt="MINOPUH" class="h-12 w-auto" />
                <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-12 w-auto" />
                <img src="/imagens/no-background/SONA.png" alt="Projecto SONA" class="h-12 w-auto" />
            </div>
        </section>

        <main id="conteudo">
            <section id="plataforma" class="scroll-mt-24 px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-10 max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[.16em] text-[#C30E20]">Plataforma</p>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-neutral-950">O que a gestão territorial passa a ter num só lugar</h2>
                        <p class="mt-3 text-neutral-600">Seis frentes do trabalho quotidiano do INOTU na Huíla, com o mesmo critério de acesso e o mesmo rasto de auditoria.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['Processos fundiários', 'Registo, tramitação e parecer com o histórico de cada etapa.'],
                            ['Gestão territorial', 'Província, município, loteamentos e instrumentos de ordenamento.'],
                            ['Controlo de acesso', 'Papéis INOTU, provincial, municipal e técnico, com permissões finas.'],
                            ['Editor geoespacial', 'Parcelas no mapa e exportação em GeoJSON, KML, DXF e CSV.'],
                            ['Loteamentos', 'Lotes, infra-estruturas e o estado de aprovação urbanística.'],
                            ['Auditoria e relatórios', 'Quem fez o quê, quando, e os indicadores para a supervisão.'],
                        ] as $item)
                            <article class="glass-card p-6 transition">
                                <h3 class="text-lg font-semibold text-neutral-950">{{ $item[0] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $item[1] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="px-4 pb-8 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
                    @foreach ([
                        ['01', 'Entrar', 'A área institucional pede credenciais. O utente consulta pelo BI, RUPE ou referência.'],
                        ['02', 'Tramitar', 'O processo muda de estado com responsável, data e o documento associado.'],
                        ['03', 'Fechar o ciclo', 'Parecer, pagamento e auditoria ficam ligados ao mesmo registo territorial.'],
                    ] as $step)
                        <article class="glass-card p-6">
                            <p class="text-sm font-bold tracking-[.18em] text-[#C30E20]">{{ $step[0] }}</p>
                            <h3 class="mt-3 text-xl font-bold text-neutral-950">{{ $step[1] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $step[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="modulos" class="scroll-mt-24 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto grid max-w-7xl items-start gap-10 lg:grid-cols-2 lg:gap-16">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[.16em] text-[#C30E20]">Sobre</p>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-neutral-950">O sistema de informação do INOTU na Huíla</h2>
                        <p class="mt-4 text-neutral-600 leading-7">O G.TERRAS gere a Base de Dados Provincial de Terras, no âmbito do Projecto SONA. Digitaliza o registo, o acompanhamento e a emissão de pareceres sobre concessão de terras.</p>
                        <ul class="mt-6 space-y-3 text-sm text-neutral-700">
                            <li class="glass-card px-4 py-3"><b>Rastreabilidade.</b> Data, hora e utilizador em cada acção.</li>
                            <li class="glass-card px-4 py-3"><b>Segurança multinível.</b> Permissões por papel e âmbito territorial.</li>
                            <li class="glass-card px-4 py-3"><b>Integração provincial.</b> Os 22 municípios da Huíla no mesmo modelo.</li>
                        </ul>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-zinc-950/60 p-6 text-white shadow-2xl backdrop-blur-xl sm:p-8">
                        <h3 class="text-lg font-semibold">Módulos em uso</h3>
                        <div class="mt-5 grid gap-3">
                            @foreach ([
                                ['Gestão de processos', 'Registo, tramitação e pareceres'],
                                ['Loteamentos e urbanismo', 'Controlo de loteamentos urbanos'],
                                ['Operações urbanísticas', 'Licenciamento e fiscalização'],
                                ['Auditoria e relatórios', 'Monitorização da actividade'],
                                ['ERP institucional', 'Financeiro, RH, compras e património'],
                            ] as $mod)
                                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                                    <p class="text-sm font-semibold">{{ $mod[0] }}</p>
                                    <p class="text-xs text-white/60">{{ $mod[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="acesso" class="scroll-mt-24 px-4 pb-20 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-4 lg:grid-cols-2">
                    <a href="{{ route('portal.home') }}" class="glass-card block p-7 transition hover:-translate-y-0.5">
                        <p class="text-xs font-bold uppercase tracking-[.16em] text-[#C30E20]">Utente</p>
                        <h2 class="mt-2 text-2xl font-bold text-neutral-950">Consultar um processo</h2>
                        <p class="mt-2 max-w-md text-sm leading-6 text-neutral-600">Acompanhe solicitações pelo Bilhete de Identidade, referência RUPE, token QR ou serviços públicos do INOTU.</p>
                        <p class="mt-5 text-sm font-semibold text-[#C30E20]">Abrir o portal do utente</p>
                    </a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="block rounded-2xl border border-white/10 bg-zinc-950/75 p-7 text-white shadow-xl backdrop-blur-xl transition hover:-translate-y-0.5">
                        <p class="text-xs font-bold uppercase tracking-[.16em] text-[#F2CB13]">Institucional</p>
                        <h2 class="mt-2 text-2xl font-bold">Entrar no G.TERRAS</h2>
                        <p class="mt-2 max-w-md text-sm leading-6 text-white/70">Técnicos, municípios e a delegação provincial trabalham com credenciais próprias e o âmbito territorial da conta.</p>
                        <p class="mt-5 text-sm font-semibold text-[#F2CB13]">{{ auth()->check() ? 'Ir para o painel' : 'Iniciar sessão' }}</p>
                    </a>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/10 bg-zinc-950/80 text-white backdrop-blur-xl">
            <div class="gov-stripe"></div>
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 sm:grid-cols-3 lg:px-8">
                <div>
                    <p class="text-base font-semibold">G.TERRAS</p>
                    <p class="mt-2 text-sm leading-6 text-white/60">Base de Dados Provincial de Terras da Província da Huíla.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-white/50">Instituição</p>
                    <p class="mt-3 text-sm leading-6 text-white/70">INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo<br>Delegação Provincial da Huíla</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-white/50">Projecto</p>
                    <p class="mt-3 text-sm leading-6 text-white/70">Projecto SONA<br>República de Angola</p>
                </div>
            </div>
            <p class="border-t border-white/10 px-4 py-4 text-center text-xs text-white/45">&copy; {{ date('Y') }} G.TERRAS — INOTU · República de Angola</p>
        </footer>

        <script>
            (function () {
                var root = document.getElementById('hero');
                var slides = root.querySelectorAll('.hero-slide');
                var dots = root.querySelectorAll('.hero-dot');
                var indexEl = document.getElementById('heroIndex');
                var total = slides.length;
                var current = 0;
                var timer;
                var duration = 7000;
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var startX = 0;
                var dragging = false;

                root.style.setProperty('--hero-ms', duration + 'ms');

                function goTo(index) {
                    current = ((index % total) + total) % total;
                    slides.forEach(function (slide, idx) {
                        var on = idx === current;
                        slide.classList.toggle('is-active', on);
                        slide.setAttribute('aria-hidden', on ? 'false' : 'true');
                    });
                    dots.forEach(function (dot, idx) {
                        var on = idx === current;
                        dot.classList.remove('is-active');
                        if (on) {
                            void dot.offsetWidth;
                            dot.classList.add('is-active');
                            dot.setAttribute('aria-current', 'true');
                        } else {
                            dot.removeAttribute('aria-current');
                        }
                    });
                    indexEl.textContent = String(current + 1).padStart(2, '0');
                    clearInterval(timer);
                    if (!reduce && !root.classList.contains('is-paused')) {
                        timer = setInterval(function () { goTo(current + 1); }, duration);
                    }
                }

                root.querySelector('[data-hero="next"]').addEventListener('click', function () { goTo(current + 1); });
                root.querySelector('[data-hero="prev"]').addEventListener('click', function () { goTo(current - 1); });
                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () { goTo(Number(dot.getAttribute('data-hero-go'))); });
                });
                root.addEventListener('mouseenter', function () { root.classList.add('is-paused'); clearInterval(timer); });
                root.addEventListener('mouseleave', function () { root.classList.remove('is-paused'); goTo(current); });
                root.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowRight') { event.preventDefault(); goTo(current + 1); }
                    if (event.key === 'ArrowLeft') { event.preventDefault(); goTo(current - 1); }
                });
                root.addEventListener('pointerdown', function (event) {
                    if (event.target.closest('a, button')) return;
                    dragging = true;
                    startX = event.clientX;
                });
                window.addEventListener('pointerup', function (event) {
                    if (!dragging) return;
                    dragging = false;
                    var delta = event.clientX - startX;
                    if (delta > 60) goTo(current - 1);
                    else if (delta < -60) goTo(current + 1);
                });

                goTo(0);
            })();

            (function () {
                var ua = navigator.userAgent.toLowerCase();
                var os = null;
                if (ua.indexOf('win') !== -1) os = 'windows';
                else if (ua.indexOf('mac') !== -1 && ua.indexOf('iphone') === -1 && ua.indexOf('ipad') === -1) os = 'mac';
                else if (ua.indexOf('linux') !== -1 && ua.indexOf('android') === -1) os = 'linux';
                if (!os || ua.indexOf('electron') !== -1) return;

                var banner = document.getElementById('desktopBanner');
                var btnAbrir = document.getElementById('bannerInstalado');
                var btnTransfer = document.getElementById('bannerTransferir');
                var bannerOS = document.getElementById('bannerOS');
                var nomes = { windows: 'Windows', mac: 'macOS', linux: 'Linux' };

                bannerOS.textContent = '— ' + nomes[os];
                banner.classList.remove('hidden');
                btnAbrir.classList.remove('hidden');
                btnTransfer.classList.remove('hidden');

                if (os !== 'windows') {
                    var linkTransfer = btnTransfer.querySelector('a');
                    linkTransfer.removeAttribute('href');
                    linkTransfer.style.opacity = '0.6';
                    linkTransfer.style.cursor = 'default';
                    document.getElementById('bannerTexto').textContent = 'G.TERRAS Desktop — em breve para ' + nomes[os];
                    bannerOS.textContent = '';
                }
            })();
        </script>
    </body>
</html>
