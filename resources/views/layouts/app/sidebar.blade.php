<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 antialiased">

        {{-- SECTION: Skip Link (acessibilidade) --}}
        <a href="#main-content" class="skip-link">Ir para o conteúdo</a>

        {{-- SECTION: Sidebar --}}
        <flux:sidebar sticky collapsible class="dark border-e border-white/10 bg-zinc-950 shadow-[8px_0_24px_rgba(0,0,0,0.12)]">

            {{-- Cabeçalho do sidebar com logótipo --}}
            <flux:sidebar.header class="border-b border-white/5 pb-3">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse />
            </flux:sidebar.header>

            <flux:sidebar.nav class="pt-4">
                {{-- ── TERRITÓRIO ── --}}
                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >
                    Dashboard
                </flux:sidebar.item>

                {{-- Municípios --}}
                @php
                    $user = auth()->user();
                    $provinciasQuery = \App\Models\Provincia::query()
                        ->where('activo', true)
                        ->with(['municipios' => fn ($query) => $query->where('activo', true)->orderBy('nome')]);

                    if ($user->isSuperAdminNacional()) {
                        $perspetiva = app(\App\Services\PerspetivaService::class);
                        if ($perspetiva->isProvincial() && $perspetiva->getProvinciaId()) {
                            $provinciasQuery->whereKey($perspetiva->getProvinciaId());
                        }
                    } elseif ($user->isMunicipal()) {
                        $provinciasQuery->whereKey($user->provincia_id);
                        $provinciasQuery->with(['municipios' => fn ($query) => $query
                            ->where('activo', true)
                            ->whereKey($user->municipio_id)
                            ->orderBy('nome')]);
                    } elseif ($user->isProvincial()) {
                        $provinciasQuery->whereKey($user->provincia_id);
                    }

                    $sidebarProvincias = $provinciasQuery->orderBy('nome')->get();
                    $activeMunicipio = request()->route('municipio');
                    $activeMunicipioSlug = $activeMunicipio instanceof \App\Models\Municipio
                        ? $activeMunicipio->slug
                        : null;
                @endphp

                <flux:sidebar.group
                    icon="map"
                    heading="Território"
                    expandable
                    :expanded="request()->routeIs('municipio.*') || request()->routeIs('municipios.*')"
                >
                    @foreach($sidebarProvincias as $provincia)
                        <flux:sidebar.group
                            :heading="$provincia->nome"
                            expandable
                            :expanded="$provincia->municipios->contains('slug', $activeMunicipioSlug)"
                        >
                            @foreach($provincia->municipios as $municipio)
                                <flux:sidebar.item
                                    :href="route('municipio.show', $municipio)"
                                    :current="$activeMunicipioSlug === $municipio->slug"
                                    wire:navigate
                                >
                                    {{ $municipio->nome }}
                                </flux:sidebar.item>
                            @endforeach
                        </flux:sidebar.group>
                    @endforeach
                </flux:sidebar.group>

                {{-- ── ERP ── --}}
                @if($user->temPermissao('financeiro.ver') || $user->temPermissao('rh.ver') || $user->temPermissao('compras.ver') || $user->temPermissao('patrimonio.ver'))
                    <flux:sidebar.group
                        icon="cog-6-tooth"
                        heading="ERP"
                        expandable
                        :expanded="request()->routeIs('erp.*')"
                    >
                        @if($user->temPermissao('financeiro.ver'))
                            <flux:sidebar.group
                                heading="Financeiro"
                                expandable
                                :expanded="request()->routeIs('erp.financeiro.*')"
                            >
                                <flux:sidebar.item
                                    :href="route('erp.financeiro.contas-gerais')"
                                    :current="request()->routeIs('erp.financeiro.contas-gerais')"
                                    wire:navigate
                                >
                                    Contas Gerais
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.financeiro.lancamentos')"
                                    :current="request()->routeIs('erp.financeiro.lancamentos')"
                                    wire:navigate
                                >
                                    Lançamentos
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.financeiro.pagamentos')"
                                    :current="request()->routeIs('erp.financeiro.pagamentos')"
                                    wire:navigate
                                >
                                    Pagamentos
                                </flux:sidebar.item>
                            </flux:sidebar.group>
                        @endif

                        @if($user->temPermissao('rh.ver'))
                            <flux:sidebar.group
                                heading="Recursos Humanos"
                                expandable
                                :expanded="request()->routeIs('erp.rh.*')"
                            >
                                <flux:sidebar.item
                                    :href="route('erp.rh.funcionarios')"
                                    :current="request()->routeIs('erp.rh.funcionarios')"
                                    wire:navigate
                                >
                                    Funcionários
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.rh.departamentos')"
                                    :current="request()->routeIs('erp.rh.departamentos')"
                                    wire:navigate
                                >
                                    Departamentos
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.rh.cargos')"
                                    :current="request()->routeIs('erp.rh.cargos')"
                                    wire:navigate
                                >
                                    Cargos
                                </flux:sidebar.item>
                            </flux:sidebar.group>
                        @endif

                        @if($user->temPermissao('compras.ver'))
                            <flux:sidebar.group
                                heading="Compras e Contratos"
                                expandable
                                :expanded="request()->routeIs('erp.compras.*')"
                            >
                                <flux:sidebar.item
                                    :href="route('erp.compras.fornecedores')"
                                    :current="request()->routeIs('erp.compras.fornecedores')"
                                    wire:navigate
                                >
                                    Fornecedores
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.compras.requisicoes')"
                                    :current="request()->routeIs('erp.compras.requisicoes')"
                                    wire:navigate
                                >
                                    Requisições
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.compras.contratos')"
                                    :current="request()->routeIs('erp.compras.contratos')"
                                    wire:navigate
                                >
                                    Contratos
                                </flux:sidebar.item>
                            </flux:sidebar.group>
                        @endif

                        @if($user->temPermissao('patrimonio.ver'))
                            <flux:sidebar.group
                                heading="Património"
                                expandable
                                :expanded="request()->routeIs('erp.patrimonio.*')"
                            >
                                <flux:sidebar.item
                                    :href="route('erp.patrimonio.bens')"
                                    :current="request()->routeIs('erp.patrimonio.bens')"
                                    wire:navigate
                                >
                                    Bens
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.patrimonio.categorias')"
                                    :current="request()->routeIs('erp.patrimonio.categorias')"
                                    wire:navigate
                                >
                                    Categorias
                                </flux:sidebar.item>
                                <flux:sidebar.item
                                    :href="route('erp.patrimonio.localizacoes')"
                                    :current="request()->routeIs('erp.patrimonio.localizacoes')"
                                    wire:navigate
                                >
                                    Localizações
                                </flux:sidebar.item>
                            </flux:sidebar.group>
                        @endif
                    </flux:sidebar.group>
                @endif

                {{-- ── GESTÃO ── --}}
                @if(auth()->user()->isNacional() || auth()->user()->isInotu() || auth()->user()->isProvincial() || auth()->user()->isAdminMunicipal())
                    <flux:sidebar.group
                        icon="cog-6-tooth"
                        heading="Gestão"
                        expandable
                        :expanded="request()->routeIs('nacional.*') || request()->routeIs('usuarios.*') || request()->routeIs('auditoria.*') || request()->routeIs('permissoes.*') || request()->routeIs('erros-sistema.*')"
                    >
                        {{-- Dashboard Nacional (apenas para usuários nacionais) --}}
                        @if(auth()->user()->isNacional())
                            <flux:sidebar.item
                                icon="home"
                                :href="route('nacional.dashboard')"
                                :current="request()->routeIs('nacional.*')"
                                wire:navigate
                            >
                                Dashboard Nacional
                            </flux:sidebar.item>
                        @endif

                        @if(auth()->user()->temPermissao('usuarios.ver'))
                            <flux:sidebar.item
                                icon="users"
                                :href="route('usuarios.index')"
                                :current="request()->routeIs('usuarios.*')"
                                wire:navigate
                            >
                                Utilizadores
                            </flux:sidebar.item>
                        @endif

                        @if(auth()->user()->isNacional() || auth()->user()->isInotu())
                            <flux:sidebar.item
                                icon="lock-closed"
                                :href="route('permissoes.index')"
                                :current="request()->routeIs('permissoes.*')"
                                wire:navigate
                            >
                                Permissões
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                icon="shield-check"
                                :href="route('auditoria.index')"
                                :current="request()->routeIs('auditoria.*')"
                                wire:navigate
                            >
                                Auditoria
                            </flux:sidebar.item>

                            @if(auth()->user()->podeAcederLixeira())
                                <flux:sidebar.item
                                    icon="trash"
                                    :href="route('lixeira.index')"
                                    :current="request()->routeIs('lixeira.*')"
                                    wire:navigate
                                >
                                    Lixeira
                                </flux:sidebar.item>
                            @endif

                            <flux:sidebar.item
                                icon="exclamation-triangle"
                                :href="route('erros-sistema.index')"
                                :current="request()->routeIs('erros-sistema.*')"
                                wire:navigate
                            >
                                Erros do Sistema
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                icon="qr-code"
                                :href="route('settings.qr-layouts')"
                                :current="request()->routeIs('settings.qr-layouts')"
                                wire:navigate
                            >
                                Layouts de QR Code
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Links de rodapé --}}
            <flux:sidebar.nav class="border-t border-white/5 pt-3 pb-1">
                <flux:sidebar.item
                    icon="arrow-left"
                    :href="route('home')"
                >
                    Voltar ao Home
                </flux:sidebar.item>
                <flux:sidebar.item
                    icon="arrow-down-tray"
                    :href="route('desktop.download')"
                    target="_blank"
                >
                    Desktop App
                </flux:sidebar.item>
                <flux:sidebar.item
                    icon="bell"
                    :href="route('notificacoes.index')"
                    :current="request()->routeIs('notificacoes.*')"
                    wire:navigate
                >
                    Notificações
                </flux:sidebar.item>
                <flux:sidebar.item
                    icon="user-circle"
                    :href="route('profile.overview')"
                    wire:navigate
                >
                    Meu Perfil
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        {{-- SECTION: Header Desktop --}}
        <flux:header class="hidden lg:flex h-16 items-center gap-4 border-b border-zinc-200 bg-white px-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Título da aplicação --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold tracking-widest text-zinc-400 dark:text-zinc-500 uppercase">G.TERRAS</span>
                <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                <span class="text-xs text-zinc-400 dark:text-zinc-500">Gestão Territorial</span>
                @if(auth()->user()->isSuperAdminNacional())
                    @php $perspetiva = app(\App\Services\PerspetivaService::class); @endphp
                    <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $perspetiva->isGlobal() ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300' : ($perspetiva->isNacional() ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300') }}">
                        {{ $perspetiva->label() }}
                    </span>
                @elseif(auth()->user()->isNacional())
                    <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                    <span class="text-xs text-angola-red font-semibold">Nacional</span>
                @elseif(auth()->user()->isProvincial() && auth()->user()->provincia)
                    <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                    <span class="text-xs text-zinc-700 dark:text-zinc-300 font-medium">{{ auth()->user()->provincia->nome }}</span>
                @elseif(auth()->user()->isMunicipal() && auth()->user()->municipio)
                    <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                    <span class="text-xs text-zinc-700 dark:text-zinc-300 font-medium">{{ auth()->user()->municipio->nome }}</span>
                @endif
            </div>

            <div class="flex-1"></div>

            {{-- Seletor de Perspetiva (Superadmin) --}}
            @if(auth()->user()->isSuperAdminNacional())
                <livewire:seletor-perspetiva key="perspetiva-header-desktop" />
            @endif

            {{-- Pesquisa --}}
            <livewire:live-search key="search-header" />

            {{-- Notificações --}}
            <livewire:notificacao-centro key="notif-header" />

            {{-- Alternar Tema (Claro / Escuro) --}}
            <button
                type="button"
                x-data
                x-on:click="$flux.dark = !$flux.dark; window.Flux?.applyAppearance ? window.Flux.applyAppearance($flux.dark ? 'dark' : 'light') : document.documentElement.classList.toggle('dark', $flux.dark)"
                class="relative inline-flex size-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                :title="$flux.dark ? 'Mudar para modo claro' : 'Mudar para modo escuro'"
                aria-label="Alternar tema visual"
            >
                {{-- Ícone Sol (quando em modo escuro) --}}
                <svg x-show="$flux.dark" x-cloak class="size-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                {{-- Ícone Lua (quando em modo claro) --}}
                <svg x-show="!$flux.dark" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            {{-- Perfil (Apenas Avatar/Iniciais no Header) --}}
            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    class="cursor-pointer"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="flex items-center gap-3 px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                                size="sm"
                            />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-50 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                                @php
                                    $user = auth()->user();
                                    $rolePrincipal = $user->roles->first();
                                @endphp
                                @if($rolePrincipal)
                                    <p class="text-xs text-angola-red truncate">{{ $rolePrincipal->descricao ?? $rolePrincipal->nome }}</p>
                                @endif
                                @if($user->isNacional())
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">Nível Nacional</p>
                                @elseif($user->isProvincial() && $user->provincia)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $user->provincia->nome }}</p>
                                @elseif($user->isMunicipal() && $user->municipio)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $user->municipio->nome }}</p>
                                @endif
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.overview')" icon="user-circle" wire:navigate>
                            Meu Perfil
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer text-red-600 dark:text-red-400"
                        >
                            Terminar Sessão
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{-- SECTION: Header Mobile --}}
        <flux:header class="lg:hidden border-b border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            @if(auth()->user()->isSuperAdminNacional())
                <livewire:seletor-perspetiva key="perspetiva-header-mobile" />
            @endif
            <livewire:live-search key="search-mobile" />
            <livewire:notificacao-centro key="notif-mobile" />

            {{-- Alternar Tema no Mobile --}}
            <button
                type="button"
                x-data
                x-on:click="$flux.dark = !$flux.dark; window.Flux?.applyAppearance ? window.Flux.applyAppearance($flux.dark ? 'dark' : 'light') : document.documentElement.classList.toggle('dark', $flux.dark)"
                class="relative inline-flex size-9 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                :title="$flux.dark ? 'Mudar para modo claro' : 'Mudar para modo escuro'"
                aria-label="Alternar tema visual"
            >
                <svg x-show="$flux.dark" x-cloak class="size-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <svg x-show="!$flux.dark" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" class="cursor-pointer" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="flex items-center gap-3 px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                                @php
                                    $user = auth()->user();
                                    $rolePrincipal = $user->roles->first();
                                @endphp
                                @if($rolePrincipal)
                                    <p class="text-xs text-angola-red truncate">{{ $rolePrincipal->descricao ?? $rolePrincipal->nome }}</p>
                                @endif
                                @if($user->isNacional())
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">Nível Nacional</p>
                                @elseif($user->isProvincial() && $user->provincia)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $user->provincia->nome }}</p>
                                @elseif($user->isMunicipal() && $user->municipio)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $user->municipio->nome }}</p>
                                @endif
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.overview')" icon="user-circle" wire:navigate>
                            Meu Perfil
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                        >
                            Terminar Sessão
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{-- SECTION: Conteúdo principal --}}
        <flux:main id="main-content" class="min-h-[calc(100vh-4rem)] w-full max-w-none bg-zinc-50/70 dark:bg-zinc-950">
            {{ $slot }}
        </flux:main>

        {{-- Toast global --}}
        <x-toast-container />

        @fluxScripts
    </body>
</html>
