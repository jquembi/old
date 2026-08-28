<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>G.TERRAS Desktop — Transferir Aplicação</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .gov-stripe { height: 4px; background: linear-gradient(90deg, #ce1126 0%, #ce1126 50%, #ce1126 50%, #ce1126 100%); }
            .download-hero { background: linear-gradient(135deg, #0c3524 0%, #145a3a 40%, #0e4a2f 100%); }
            .feature-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
            .feature-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
            .pulse-download { animation: pulse-green 2s infinite; }
            @keyframes pulse-green {
                0%, 100% { box-shadow: 0 0 0 0 rgba(0, 151, 57, 0.4); }
                50% { box-shadow: 0 0 0 12px rgba(0, 151, 57, 0); }
            }
        </style>
    </head>
    <body class="bg-white text-neutral-900 antialiased font-sans">

        {{-- Faixa tricolor topo --}}
        <div class="gov-stripe"></div>

        {{-- Header --}}
        <header class="sticky top-0 z-50 bg-white border-b border-neutral-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-10 w-auto" />
                        <div class="hidden sm:block w-px h-8 bg-neutral-300"></div>
                        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-10 w-auto" />
                        <div class="hidden md:flex flex-col leading-tight">
                            <span class="text-sm font-semibold text-neutral-900">G.TERRAS</span>
                            <span class="text-[11px] text-neutral-500">Base de Dados Provincial de Terras</span>
                        </div>
                    </div>

                    <nav class="flex items-center gap-2">
                        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 hover:text-neutral-900 transition-colors">
                            Página Inicial
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-[#ce1126] hover:bg-[#a50e1e] rounded-md transition-colors">
                                Painel de Controlo
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2 text-sm font-semibold text-white bg-[#ce1126] hover:bg-[#a50e1e] rounded-md transition-colors">
                                Iniciar Sessão
                            </a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        {{-- Hero Section --}}
        <section class="download-hero relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><path d=%22M0 0h60v60H0z%22 fill=%22none%22/><path d=%22M30 0v60M0 30h60%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>');"></div>

            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center">
                {{-- Icon --}}
                <div class="mx-auto mb-8 w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                    G.TERRAS Desktop <span class="text-red-300">Pro</span>
                </h1>
                <p class="text-lg text-red-100/80 max-w-2xl mx-auto mb-10">
                    Aplicação desktop para gestão territorial avançada. Acesso offline aos ficheiros,
                    visualização local de documentos e integração directa com o sistema G.TERRAS.
                </p>

                {{-- Download Button --}}
                <div class="flex flex-col items-center gap-4">
                    <a href="{{ asset('downloads/G.TERRAS-Desktop-Setup.exe') }}"
                       class="pulse-download inline-flex items-center gap-3 px-8 py-4 text-lg font-bold text-white bg-[#ce1126] hover:bg-[#a50e1e] rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Transferir para Windows
                    </a>
                    <div class="flex items-center gap-4 text-sm text-red-200/60">
                        <span>Versão 1.0.0</span>
                        <span class="w-1 h-1 rounded-full bg-red-200/40"></span>
                        <span>Windows 10/11 (64-bit)</span>
                        <span class="w-1 h-1 rounded-full bg-red-200/40"></span>
                        <span>~79 MB</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-16 lg:py-20 bg-neutral-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-center text-neutral-900 mb-12">Funcionalidades da Aplicação Desktop</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Feature 1 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Transferência de Ficheiros</h3>
                        <p class="text-sm text-neutral-600">Transfira croquis, pareceres e instrumentos directamente para o seu computador com cache inteligente.</p>
                    </div>

                    {{-- Feature 2 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Visualização Local</h3>
                        <p class="text-sm text-neutral-600">Abra PDFs, imagens e ficheiros GIS directamente nas aplicações nativas do Windows.</p>
                    </div>

                    {{-- Feature 3 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Autenticação Segura</h3>
                        <p class="text-sm text-neutral-600">Login integrado com o sistema G.TERRAS via API Sanctum com tokens seguros.</p>
                    </div>

                    {{-- Feature 4 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Notificações Nativas</h3>
                        <p class="text-sm text-neutral-600">Receba alertas do Windows quando transferências concluírem ou para novos documentos.</p>
                    </div>

                    {{-- Feature 5 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Cache Inteligente</h3>
                        <p class="text-sm text-neutral-600">Ficheiros transferidos ficam em cache local para acesso rápido mesmo sem ligação à internet.</p>
                    </div>

                    {{-- Feature 6 --}}
                    <div class="feature-card bg-white rounded-xl p-6 border border-neutral-200">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-neutral-900 mb-2">Segurança Reforçada</h3>
                        <p class="text-sm text-neutral-600">Proteção contra path traversal, tokens encriptados e comunicação segura com a API.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Installation Instructions --}}
        <section class="py-16 lg:py-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-center text-neutral-900 mb-12">Como Instalar</h2>

                <div class="space-y-8">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ce1126] text-white font-bold flex items-center justify-center text-sm">1</div>
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-1">Transferir o Instalador</h3>
                            <p class="text-sm text-neutral-600">Clique no botão "Transferir para Windows" acima. O ficheiro <code class="bg-neutral-100 px-1.5 py-0.5 rounded text-xs">G.TERRAS-Desktop-1.0.0-Setup.exe</code> será descarregado.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ce1126] text-white font-bold flex items-center justify-center text-sm">2</div>
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-1">Executar o Instalador</h3>
                            <p class="text-sm text-neutral-600">Faça duplo clique no ficheiro transferido. O Windows pode mostrar um aviso de segurança — clique em <strong>"Mais informações"</strong> e depois <strong>"Executar mesmo assim"</strong>.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ce1126] text-white font-bold flex items-center justify-center text-sm">3</div>
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-1">Seguir o Assistente</h3>
                            <p class="text-sm text-neutral-600">Escolha o directório de instalação (ou aceite o predefinido) e conclua a instalação. Um atalho será criado no ambiente de trabalho e no menu Iniciar.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#ce1126] text-white font-bold flex items-center justify-center text-sm">4</div>
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-1">Iniciar Sessão</h3>
                            <p class="text-sm text-neutral-600">Abra a aplicação e utilize as mesmas credenciais do sistema G.TERRAS para iniciar sessão. A aplicação ligará automaticamente ao servidor.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- System Requirements --}}
        <section class="py-16 lg:py-20 bg-neutral-50 border-t border-neutral-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-center text-neutral-900 mb-8">Requisitos do Sistema</h2>

                <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden divide-y divide-neutral-100">
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm font-medium text-neutral-700">Sistema Operativo</span>
                        <span class="text-sm text-neutral-900">Windows 10 / 11 (64-bit)</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm font-medium text-neutral-700">Memória RAM</span>
                        <span class="text-sm text-neutral-900">4 GB (mínimo)</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm font-medium text-neutral-700">Espaço em Disco</span>
                        <span class="text-sm text-neutral-900">200 MB livres</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm font-medium text-neutral-700">Ligação à Internet</span>
                        <span class="text-sm text-neutral-900">Necessária para autenticação e sincronização</span>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <span class="text-sm font-medium text-neutral-700">Processador</span>
                        <span class="text-sm text-neutral-900">Intel / AMD 64-bit</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-neutral-900 text-neutral-400 py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-sm">&copy; {{ date('Y') }} INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo</p>
                <p class="text-xs mt-1 text-neutral-500">Delegação Provincial da Huíla &mdash; G.TERRAS v1.0</p>
            </div>
        </footer>

    </body>
</html>
