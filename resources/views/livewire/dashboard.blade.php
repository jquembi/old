<div class="px-4 md:px-6 lg:px-8 py-8 space-y-8">

    @php
        $contextLabel = $user->isInotu()
            ? 'INOTU'
            : ($user->isProvincial() ? $user->provincia?->nome : $user->municipio?->nome);
        $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();
    @endphp

    {{-- SECTION: Cabeçalho / Saudação --}}
    <x-ui.page-header
        :eyebrow="'GTerra · '.$contextLabel"
        :title="'Bom dia, '.$firstName"
        :description="now()->locale('pt')->isoFormat('dddd, D [de] MMMM [de] YYYY')"
    >
        <x-slot:actions>
            <a href="{{ route('municipios.index') }}" wire:navigate class="btn-primary shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Ver Municípios
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- SECTION: Métricas principais --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Municípios --}}
        <div class="card stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="card-icon-badge card-icon-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <p class="card-value text-2xl font-bold">{{ $stats['total_municipios'] }}</p>
            <p class="card-subtitle text-xs font-medium mt-0.5 uppercase tracking-wide">Municípios</p>
        </div>

        {{-- Instrumentos --}}
        <div class="card stat-card relative">
            <div class="flex items-center justify-between mb-3">
                <div class="card-icon-badge card-icon-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="card-value text-2xl font-bold">{{ $stats['total_instrumentos'] }}</p>
            <p class="card-subtitle text-xs font-medium mt-0.5 uppercase tracking-wide">Instrumentos</p>
        </div>

        {{-- Loteamentos --}}
        <div class="card stat-card relative">
            <div class="flex items-center justify-between mb-3">
                <div class="card-icon-badge card-icon-yellow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="card-value text-2xl font-bold">{{ $stats['total_loteamentos'] }}</p>
            <p class="card-subtitle text-xs font-medium mt-0.5 uppercase tracking-wide">Loteamentos</p>
        </div>

        {{-- Utilizadores --}}
        @if($user->isInotu() || $user->isProvincial() || $user->isAdminMunicipal())
        <div class="card stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="card-icon-badge card-icon-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <p class="card-value text-2xl font-bold">{{ $stats['total_usuarios'] }}</p>
            <p class="card-subtitle text-xs font-medium mt-0.5 uppercase tracking-wide">Utilizadores</p>
        </div>
        @else
        <div class="card stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="card-icon-badge card-icon-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="card-value text-2xl font-bold">{{ $stats['total_operacoes'] }}</p>
            <p class="card-subtitle text-xs font-medium mt-0.5 uppercase tracking-wide">Operações</p>
        </div>
        @endif
    </div>

    {{-- SECTION: Acesso rápido por módulo --}}
    <div>
        <h2 class="section-heading">Acesso Rápido</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Municípios --}}
            <a href="{{ route('municipios.index') }}" class="group block" wire:navigate>
                <div class="card quick-link card-hover">
                    <div class="flex items-start gap-4">
                        <div class="card-icon-badge card-icon-red">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="card-title font-semibold text-sm">Municípios</p>
                            <p class="card-subtitle text-xs mt-0.5 line-clamp-2">Instrumentos, cartas e operações urbanísticas por município</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-angola-red group-hover:gap-2 transition-all">
                        Aceder <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>

            {{-- Gestão de Utilizadores --}}
            @if($user->isInotu() || $user->isProvincial() || $user->isAdminMunicipal())
            <a href="{{ route('usuarios.index') }}" class="group block" wire:navigate>
                <div class="card quick-link card-hover">
                    <div class="flex items-start gap-4">
                        <div class="card-icon-badge card-icon-black">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="card-title font-semibold text-sm">Utilizadores</p>
                            <p class="card-subtitle text-xs mt-0.5 line-clamp-2">Gerir contas de acesso, funções e permissões do sistema</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-zinc-600 group-hover:gap-2 transition-all">
                        Aceder <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            @endif

            {{-- Estatísticas (card informativo) --}}
            <div class="card quick-link">
                <div class="flex items-start gap-4 mb-4">
                    <div class="card-icon-badge card-icon-yellow">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    </div>
                    <div>
                        <p class="card-title font-semibold text-sm">Resumo Geral</p>
                        <p class="card-subtitle text-xs mt-0.5">Totais por categoria</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="mini-stat">
                        <p class="text-xs text-zinc-600">Operações</p>
                        <p class="text-base font-bold text-zinc-900">{{ $stats['total_operacoes'] }}</p>
                    </div>
                    <div class="mini-stat">
                        <p class="text-xs text-zinc-600">Proj. SONA</p>
                        <p class="text-base font-bold text-zinc-900">{{ $stats['total_sona'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Auditoria --}}
            @if($user->isInotu())
            <a href="{{ route('auditoria.index') }}" class="group block" wire:navigate>
                <div class="card quick-link card-hover">
                    <div class="flex items-start gap-4">
                        <div class="card-icon-badge card-icon-black">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="card-title font-semibold text-sm">Auditoria</p>
                            <p class="card-subtitle text-xs mt-0.5 line-clamp-2">Monitorar acções dos utilizadores e alterações no sistema</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-xs font-semibold text-zinc-600 group-hover:gap-2 transition-all">
                        Aceder <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            @endif

        </div>
    </div>
</div>

