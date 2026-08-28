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
            .gov-stripe { height: 4px; background: linear-gradient(90deg, #ce1126 0%, #ce1126 50%, #000000 50%, #000000 100%); }
            .gov-hero-bg { background: linear-gradient(135deg, #0a0a0a 0%, #1a0005 40%, #0f0000 100%); }
            .gov-section-accent { border-top: 3px solid #ce1126; }
            .gov-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
            .gov-card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
            .stat-number { font-variant-numeric: tabular-nums; }

            /* ── Carrossel peek ── */
            .carousel { position: relative; width: 100%; max-width: 900px; margin: 0 auto 2.5rem; overflow: hidden; border-radius: 1.5rem; }
            .carousel-track { display: flex; transition: transform 0.7s cubic-bezier(.4,0,.2,1); will-change: transform; }
            .carousel-slide { min-width: 60%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; padding: 1.5rem 1rem; transition: opacity .5s ease, transform .5s ease; opacity: .35; transform: scale(.82); filter: blur(1px); }
            .carousel-slide.active { opacity: 1; transform: scale(1); filter: blur(0); }
            .carousel-slide img { max-height: 300px; width: auto; max-width: 90%; object-fit: contain; border-radius: 1rem; background: white; padding: 1.5rem; box-shadow: 0 8px 32px rgba(0,0,0,.18), 0 0 60px rgba(206,17,38,.08); transition: transform .4s ease, box-shadow .4s ease; }
            .carousel-slide.active img { box-shadow: 0 12px 48px rgba(0,0,0,.25), 0 0 80px rgba(206,17,38,.12); }
            .carousel-slide.active img:hover { transform: scale(1.03); }
            .carousel-dots { display: flex; justify-content: center; gap: .625rem; margin-top: 1.25rem; }
            .carousel-dot { width: 11px; height: 11px; border-radius: 50%; background: rgba(255,255,255,.25); border: 2px solid rgba(255,255,255,.15); cursor: pointer; transition: all .3s ease; padding: 0; }
            .carousel-dot.active { background: #ce1126; border-color: #ce1126; transform: scale(1.35); box-shadow: 0 0 8px rgba(206,17,38,.5); }
            .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,.12); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,.2); color: white; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .25s ease; z-index: 2; }
            .carousel-btn:hover { background: rgba(255,255,255,.25); transform: translateY(-50%) scale(1.1); box-shadow: 0 0 16px rgba(255,255,255,.1); }
            .carousel-btn.prev { left: .75rem; }
            .carousel-btn.next { right: .75rem; }
            @media (min-width: 640px) { .carousel-slide img { max-height: 340px; padding: 2rem; } }
            @media (min-width: 1024px) { .carousel { max-width: 1000px; } .carousel-slide img { max-height: 380px; } .carousel-slide { min-width: 55%; } }
            @media (max-width: 639px) { .carousel-slide { min-width: 72%; } .carousel-slide img { max-height: 220px; padding: 1rem; } }
        </style>
    </head>
    <body class="bg-white text-neutral-900 antialiased font-sans">

        {{-- Faixa tricolor topo (Angola) --}}
        <div class="gov-stripe"></div>

        {{-- Header institucional --}}
        <header class="sticky top-0 z-50 bg-white border-b border-neutral-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Logo institucional --}}
                    <div class="flex items-center gap-3">
                        <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-10 w-auto" />
                        <div class="hidden sm:block w-px h-8 bg-neutral-300"></div>
                        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-10 w-auto" />
                        <div class="hidden md:flex flex-col leading-tight">
                            <span class="text-sm font-semibold text-neutral-900">G.TERRAS</span>
                            <span class="text-[11px] text-neutral-500">Base de Dados Provincial de Terras</span>
                        </div>
                    </div>

                    {{-- Navegação --}}
                    @if (Route::has('login'))
                        <nav class="flex items-center gap-2">
                            <a href="{{ route('portal.home') }}" class="hidden px-4 py-2 text-sm font-semibold text-neutral-700 hover:text-[#ce1126] sm:inline-flex">
                                Portal do Utente
                            </a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-[#ce1126] hover:bg-[#a50e1e] rounded-md transition-colors">
                                    Painel de Controlo
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-[#ce1126] hover:bg-[#a50e1e] rounded-md transition-colors">
                                    Iniciar Sessão
                                </a>
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </header>

        {{-- Hero Section --}}
        <section class="relative overflow-hidden gov-hero-bg">
            {{-- Padrão decorativo sutil --}}
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><path d=%22M0 0h60v60H0z%22 fill=%22none%22/><path d=%22M30 0v60M0 30h60%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>');"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                {{-- Carrossel de logos institucionais --}}
                <div class="carousel" id="heroCarousel">
                    <button class="carousel-btn prev" onclick="carouselPrev()" aria-label="Anterior">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <div class="carousel-track" id="carouselTrack">
                        <div class="carousel-slide">
                            <img src="/imagens/insignia.jpg" alt="República de Angola" />
                        </div>
                        <div class="carousel-slide">
                            <img src="/imagens/minopuh.jpg" alt="MINOPUH — Ministério das Obras Públicas, Urbanismo e Habitação" />
                        </div>
                        <div class="carousel-slide">
                            <img src="/imagens/inotu.jpg" alt="INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo" />
                        </div>
                        <div class="carousel-slide">
                            <img src="/imagens/sona.jpg" alt="Projecto SONA — Requalificação e Reconversão Urbana" />
                        </div>
                    </div>
                    <button class="carousel-btn next" onclick="carouselNext()" aria-label="Próximo">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <div class="carousel-dots" id="carouselDots">
                        <button class="carousel-dot active" onclick="carouselGoTo(0)" aria-label="Slide 1"></button>
                        <button class="carousel-dot" onclick="carouselGoTo(1)" aria-label="Slide 2"></button>
                        <button class="carousel-dot" onclick="carouselGoTo(2)" aria-label="Slide 3"></button>
                        <button class="carousel-dot" onclick="carouselGoTo(3)" aria-label="Slide 4"></button>
                    </div>
                </div>

                {{-- Título principal --}}
                <div class="text-center max-w-4xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-sm text-red-100 mb-6 backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21" />
                        </svg>
                        Instituto Nacional de Ordenamento do Território e Urbanismo
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight tracking-tight mb-5">
                        Base de Dados Provincial de<br>
                        <span class="text-red-400">Gestão de Terras</span>
                    </h1>

                    <p class="text-base sm:text-lg text-white/75 leading-relaxed mb-10 max-w-2xl mx-auto">
                        Plataforma digital para a gestão integrada de processos fundiários, controlo de loteamentos e acompanhamento de operações urbanísticas na Província da Huíla.
                    </p>

                    {{-- CTA --}}
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-7 py-3 text-base font-semibold text-black bg-white hover:bg-red-50 rounded-lg transition-colors shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                                </svg>
                                Aceder ao Painel
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-7 py-3 text-base font-semibold text-black bg-white hover:bg-red-50 rounded-lg transition-colors shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Iniciar Sessão
                                </a>
                            @endif
                        @endauth
                    </div>

                    {{-- Smart Desktop Banner --}}
                    <div id="desktopBanner" class="hidden mt-8">
                        {{-- App instalada → abrir --}}
                        <div id="bannerInstalado" class="hidden mb-2">
                            <a href="gterras://open" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-red-300 bg-white/10 hover:bg-white/15 border border-red-400/30 rounded-xl transition-all backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                </svg>
                                Abrir no G.TERRAS Desktop
                            </a>
                        </div>

                        {{-- App não instalada → sugerir download --}}
                        <div id="bannerTransferir" class="hidden">
                            <a href="{{ route('desktop.download') }}" class="inline-flex items-center gap-3 px-6 py-3 text-sm font-semibold text-red-300 bg-white/10 hover:bg-white/15 border border-red-400/30 rounded-xl transition-all backdrop-blur-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                <span id="bannerTexto">Transferir G.TERRAS Desktop</span>
                                <span id="bannerOS" class="text-red-200/60 text-xs"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divisor ondulado --}}
            <div class="absolute bottom-0 inset-x-0">
                <svg viewBox="0 0 1440 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                    <path d="M0 56h1440V20c-240 24-480 36-720 36S240 44 0 20v36Z" fill="#f9fafb"/>
                </svg>
            </div>
        </section>

        {{-- Secção de Funcionalidades --}}
        <section class="py-20 lg:py-24 bg-neutral-50 gov-section-accent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 mb-3">
                        Gestão Territorial Integrada
                    </h2>
                    <div class="w-16 h-1 bg-[#ce1126] mx-auto rounded-full mb-4"></div>
                    <p class="text-base text-neutral-600 max-w-2xl mx-auto">
                        Uma plataforma completa para digitalizar e optimizar os processos de gestão de terras na Província da Huíla.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Feature 1 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Processos Fundiários</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Registo, acompanhamento e parecer de processos fundiários com rastreabilidade completa de cada etapa do fluxo.
                        </p>
                    </div>

                    {{-- Feature 2 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Gestão Territorial</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Organização por província e município, controlo de loteamentos e instrumentos de gestão territorial.
                        </p>
                    </div>

                    {{-- Feature 3 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Controlo de Acesso</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Sistema hierárquico de permissões por nível (INOTU, Provincial, Municipal, Técnico) com auditoria completa.
                        </p>
                    </div>

                    {{-- Feature 4 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Editor Geoespacial</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Desenho e edição de parcelas num mapa interactivo com exportação em múltiplos formatos (GeoJSON, KML, DXF, CSV).
                        </p>
                    </div>

                    {{-- Feature 5 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Loteamentos</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Controlo urbanístico de loteamentos com registo de lotes, infra-estruturas e estado de aprovação.
                        </p>
                    </div>

                    {{-- Feature 6 --}}
                    <div class="bg-white rounded-xl p-7 border border-neutral-200 gov-card-hover">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ce1126]/10 mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 mb-2">Auditoria & Relatórios</h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Monitorização e análise de dados com registo completo de todas as acções dos utilizadores.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Secção Sobre o Sistema --}}
        <section class="py-20 lg:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 mb-3">
                            Sobre o G.TERRAS
                        </h2>
                        <div class="w-16 h-1 bg-[#ce1126] rounded-full mb-6"></div>
                        <p class="text-base text-neutral-600 leading-relaxed mb-5">
                            O G.TERRAS é o sistema de informação do INOTU para a gestão da Base de Dados Provincial de Terras da Província da Huíla, integrado no âmbito do Projecto SONA.
                        </p>
                        <p class="text-sm text-neutral-500 leading-relaxed mb-8">
                            Desenvolvido para digitalizar e modernizar os processos de gestão fundiária, o sistema permite o registo, acompanhamento e emissão de pareceres sobre processos de concessão de terras, garantindo transparência e eficiência na administração territorial.
                        </p>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center w-7 h-7 rounded-md bg-[#ce1126]/10 mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-neutral-900">Rastreabilidade Total</h4>
                                    <p class="text-sm text-neutral-500">Cada acção é registada com data, hora e utilizador responsável</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center w-7 h-7 rounded-md bg-[#ce1126]/10 mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-neutral-900">Segurança Multinivel</h4>
                                    <p class="text-sm text-neutral-500">Permissões granulares por papel e nível hierárquico</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center w-7 h-7 rounded-md bg-[#ce1126]/10 mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#ce1126]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-neutral-900">Integração Provincial</h4>
                                    <p class="text-sm text-neutral-500">Cobertura de todos os 22 municípios da Província da Huíla</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Painel de módulos --}}
                    <div class="bg-[#111111] rounded-2xl p-7 lg:p-9 text-white">
                        <h3 class="text-lg font-semibold mb-6">Módulos do Sistema</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-4 bg-white/8 rounded-lg p-4 border border-white/5">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Gestão de Processos</h4>
                                    <p class="text-xs text-white/50">Registo, tramitação e pareceres</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 bg-white/8 rounded-lg p-4 border border-white/5">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Loteamentos & Urbanismo</h4>
                                    <p class="text-xs text-white/50">Controlo de loteamentos urbanos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 bg-white/8 rounded-lg p-4 border border-white/5">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Operações Urbanísticas</h4>
                                    <p class="text-xs text-white/50">Licenciamento e fiscalização</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 bg-white/8 rounded-lg p-4 border border-white/5">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-sm">Auditoria & Relatórios</h4>
                                    <p class="text-xs text-white/50">Monitorização e análise de dados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Footer institucional --}}
        <footer class="bg-[#0a0a0a] border-t border-zinc-800">
            {{-- Faixa tricolor inferior --}}
            <div class="gov-stripe"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                {{-- Logos institucionais --}}
                <div class="flex flex-wrap items-center justify-center gap-6 mb-10 pb-8 border-b border-zinc-800">
                    <div class="bg-white rounded-xl px-5 py-4 shadow-md">
                        <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-14 w-auto" />
                    </div>
                    <div class="bg-white rounded-xl px-5 py-4 shadow-md">
                        <img src="/imagens/no-background/MINOPUH.png" alt="MINOPUH" class="h-14 w-auto" />
                    </div>
                    <div class="bg-white rounded-xl px-5 py-4 shadow-md">
                        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-14 w-auto" />
                    </div>
                    <div class="bg-white rounded-xl px-5 py-4 shadow-md">
                        <img src="/imagens/no-background/SONA.png" alt="Projecto SONA" class="h-14 w-auto" />
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-12 w-auto bg-white rounded-lg p-1.5" />
                            <span class="text-base font-semibold text-white">G.TERRAS</span>
                        </div>
                        <p class="text-sm text-neutral-400 leading-relaxed">
                            Sistema de Gestão da Base de Dados Provincial de Terras da Província da Huíla.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-widest mb-4">Instituição</h4>
                        <ul class="space-y-2 text-sm text-neutral-400">
                            <li>INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo</li>
                            <li>Delegação Provincial da Huíla</li>
                            <li>República de Angola</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-widest mb-4">Projecto</h4>
                        <ul class="space-y-2 text-sm text-neutral-400">
                            <li>Projecto SONA</li>
                            <li>Gestão Fundiária Provincial</li>
                            <li>Província da Huíla, Angola</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-zinc-800 text-center">
                    <p class="text-xs text-neutral-500">
                        &copy; {{ date('Y') }} G.TERRAS — INOTU · Instituto Nacional de Ordenamento do Território e Urbanismo · República de Angola
                    </p>
                </div>
            </div>
        </footer>

        {{-- Carrossel JS --}}
        <script>
            (function() {
                var track  = document.getElementById('carouselTrack');
                var slides = track.querySelectorAll('.carousel-slide');
                var dots   = document.querySelectorAll('#carouselDots .carousel-dot');
                var total  = slides.length;
                var current = 0;
                var timer;

                function goTo(i) {
                    current = ((i % total) + total) % total;

                    // Calculate offset: center the active slide
                    // Each slide width as % of container
                    var slideEl = slides[0];
                    var containerW = track.parentElement.offsetWidth;
                    var slideW = slideEl.offsetWidth;
                    var offset = (current * slideW) - (containerW / 2) + (slideW / 2);
                    track.style.transform = 'translateX(' + (-offset) + 'px)';

                    // Update active classes
                    slides.forEach(function(s, idx) { s.classList.toggle('active', idx === current); });
                    dots.forEach(function(d, idx) { d.classList.toggle('active', idx === current); });
                    resetTimer();
                }

                function resetTimer() {
                    clearInterval(timer);
                    timer = setInterval(function() { goTo(current + 1); }, 4000);
                }

                // Recalc on resize
                var resizeTimeout;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function() { goTo(current); }, 150);
                });

                window.carouselGoTo  = goTo;
                window.carouselNext  = function() { goTo(current + 1); };
                window.carouselPrev  = function() { goTo(current - 1); };

                // Init after layout
                goTo(0);
                resetTimer();
            })();

            // ── Deteção de SO e banner app desktop ───────────────
            // Nota: não invocamos gterras:// automaticamente para evitar
            // o diálogo do browser "Este site está a tentar abrir G.TERRAS Desktop".
            // O utilizador abre a app clicando explicitamente no botão.
            (function() {
                var ua = navigator.userAgent.toLowerCase();
                var os = null;
                if (ua.indexOf('win') !== -1) os = 'windows';
                else if (ua.indexOf('mac') !== -1 && ua.indexOf('iphone') === -1 && ua.indexOf('ipad') === -1) os = 'mac';
                else if (ua.indexOf('linux') !== -1 && ua.indexOf('android') === -1) os = 'linux';

                // Não mostrar em mobile, SO não suportado ou dentro do G.TERRAS Desktop
                if (!os || ua.indexOf('electron') !== -1) return;

                var banner      = document.getElementById('desktopBanner');
                var btnAbrir    = document.getElementById('bannerInstalado');
                var btnTransfer = document.getElementById('bannerTransferir');
                var bannerOS    = document.getElementById('bannerOS');
                var nomes       = { windows: 'Windows', mac: 'macOS', linux: 'Linux' };

                bannerOS.textContent = '— ' + nomes[os];

                // Mostrar banner: botão "Abrir" sempre visível.
                // O botão "Transferir" visível apenas para Windows (versão disponível).
                banner.classList.remove('hidden');
                btnAbrir.classList.remove('hidden');
                btnTransfer.classList.remove('hidden');

                if (os !== 'windows') {
                    var linkTransfer = btnTransfer.querySelector('a');
                    linkTransfer.removeAttribute('href');
                    linkTransfer.style.opacity = '0.5';
                    linkTransfer.style.cursor  = 'default';
                    document.getElementById('bannerTexto').textContent = 'G.TERRAS Desktop — Em breve para ' + nomes[os];
                    bannerOS.textContent = '';
                }
            })();
        </script>
    </body>
</html>
