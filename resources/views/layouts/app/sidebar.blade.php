<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 antialiased">

        {{-- SECTION: Skip Link (acessibilidade) --}}
        <a href="#main-content" class="skip-link">Ir para o conteúdo</a>

        {{-- SECTION: Sidebar --}}
        <flux:sidebar sticky collapsible class="dark border-e border-white/5 bg-zinc-950">

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
                    if ($user->isMunicipal()) {
                        $sidebarMunicipios = \App\Models\Municipio::where('id', $user->municipio_id)->where('activo', true)->get();
                    } elseif ($user->isProvincial()) {
                        $sidebarMunicipios = \App\Models\Municipio::where('provincia_id', $user->provincia_id)->where('activo', true)->orderBy('nome')->get();
                    } else {
                        $sidebarMunicipios = \App\Models\Municipio::where('activo', true)->orderBy('nome')->get();
                    }
                @endphp

                <flux:sidebar.group
                    icon="map"
                    heading="Municípios"
                    expandable
                    :expanded="request()->routeIs('municipio.*') || request()->routeIs('municipios.*')"
                >
                    @foreach($sidebarMunicipios as $mun)
                        <flux:sidebar.item
                            :href="route('municipio.show', $mun)"
                            :current="request()->routeIs('municipio.*') && request()->route('municipio')?->slug === $mun->slug"
                            wire:navigate
                        >
                            {{ $mun->nome }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>

                {{-- ── GESTÃO ── --}}
                @if(auth()->user()->isInotu() || auth()->user()->isProvincial() || auth()->user()->isAdminMunicipal())
                    <flux:sidebar.group
                        icon="cog-6-tooth"
                        heading="Gestão"
                        expandable
                        :expanded="request()->routeIs('usuarios.*') || request()->routeIs('auditoria.*') || request()->routeIs('permissoes.*') || request()->routeIs('erros-sistema.*')"
                    >
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

                        @if(auth()->user()->isInotu())
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
        <flux:header class="hidden lg:flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 h-14">
            {{-- Título da aplicação --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold tracking-widest text-zinc-400 dark:text-zinc-500 uppercase">G.TERRAS</span>
                <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700"></span>
                <span class="text-xs text-zinc-400 dark:text-zinc-500">Gestão Territorial</span>
            </div>

            <div class="flex-1"></div>

            {{-- Pesquisa --}}
            <livewire:live-search key="search-header" />

            {{-- Notificações --}}
            <livewire:notificacao-centro key="notif-header" />

            {{-- Toggle dark mode --}}
            <flux:button icon="moon" size="sm" variant="ghost" x-on:click="$flux.dark ? $flux.dark = false : $flux.dark = true" title="Alternar modo escuro" />

            {{-- Perfil --}}
            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="!text-sm"
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
        <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <livewire:live-search key="search-mobile" />
            <livewire:notificacao-centro key="notif-mobile" />

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="flex items-center gap-3 px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
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
        <flux:main id="main-content" class="w-full max-w-none">
            {{ $slot }}
        </flux:main>

        {{-- Toast global --}}
        <x-toast-container />

        @fluxScripts
    </body>
</html>
