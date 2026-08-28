<div>
    @include('partials.settings-heading')

    <x-pages::settings.layout heading="Visão Geral" subheading="" :wide="true">

        @php
            $nivel = $user->nivelMinimo();
            // Mapa de acentos por nível — sempre na paleta da marca (vermelho/zinc)
            $accentMap = [
                1 => ['from-red-700 to-zinc-900', 'bg-red-600', 'text-red-600 dark:text-red-400', 'bg-red-50 dark:bg-red-950/30', 'border-red-200 dark:border-red-800/40'],
                2 => ['from-zinc-800 to-zinc-950', 'bg-zinc-700', 'text-zinc-600 dark:text-zinc-400', 'bg-zinc-100 dark:bg-zinc-800/60', 'border-zinc-200 dark:border-zinc-700'],
                3 => ['from-red-800 to-zinc-950', 'bg-red-700', 'text-red-600 dark:text-red-400', 'bg-red-50 dark:bg-red-950/30', 'border-red-200 dark:border-red-800/40'],
            ];
            $accent = $accentMap[$nivel] ?? ['from-zinc-700 to-zinc-900', 'bg-zinc-600', 'text-zinc-600 dark:text-zinc-400', 'bg-zinc-100 dark:bg-zinc-800/60', 'border-zinc-200 dark:border-zinc-700'];
        @endphp

        <div class="w-full max-w-none space-y-5">

            {{-- ══ SECÇÃO TOPO: Perfil + Stats lado a lado ══ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- ── Cartão de Identidade (ocupa 2 cols) ── --}}
                <div class="lg:col-span-2 relative overflow-hidden rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900">
                    {{-- Barra de acento superior --}}
                    <div class="h-1 bg-gradient-to-r {{ $accent[0] }}"></div>

                    <div class="p-6">
                        <div class="flex items-start gap-5">
                            {{-- Avatar --}}
                            <div class="relative shrink-0">
                                @if($user->foto_perfil)
                                    <img src="{{ Storage::url($user->foto_perfil) }}" alt="{{ $user->name }}"
                                         class="w-[72px] h-[72px] rounded-2xl object-cover shadow-sm" />
                                @else
                                    <div class="w-[72px] h-[72px] rounded-2xl bg-gradient-to-br {{ $accent[0] }} flex items-center justify-center shadow-sm">
                                        <span class="text-2xl font-bold text-white select-none">{{ $user->initials() }}</span>
                                    </div>
                                @endif
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-zinc-900 {{ $user->activo ? 'bg-red-500' : 'bg-red-500' }}"></span>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0 space-y-3">
                                <div>
                                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50 tracking-tight truncate">{{ $user->name }}</h2>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate mt-0.5">{{ $user->email }}</p>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($user->roles->isNotEmpty())
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold {{ $accent[3] }} {{ $accent[2] }} {{ $accent[4] }} border">
                                            <flux:icon.shield-check class="w-3.5 h-3.5" />
                                            {{ $user->roles->pluck('descricao')->implode(', ') }}
                                        </span>
                                    @endif
                                    @if($user->roles->pluck('grupo')->unique()->filter()->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            <flux:icon.user-group class="w-3.5 h-3.5" />
                                            {{ $user->roles->pluck('grupo')->unique()->filter()->implode(', ') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Localização --}}
                                <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="inline-flex items-center gap-1">
                                        <flux:icon.map-pin class="w-3.5 h-3.5" />
                                        {{ $user->provincia?->nome ?? 'Sem província' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <flux:icon.building-office class="w-3.5 h-3.5" />
                                        {{ $user->municipio?->nome ?? 'Todos os municípios' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rodapé do cartão --}}
                    <div class="border-t border-zinc-100 dark:border-zinc-800 px-6 py-3 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                        <div class="flex items-center gap-3 text-xs text-zinc-400">
                            <span class="font-mono bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-zinc-500 dark:text-zinc-400">ID #{{ $user->id }}</span>
                            <span>Desde {{ $user->created_at?->translatedFormat('M Y') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400 font-medium">
                                    <flux:icon.check-circle class="w-3.5 h-3.5" />
                                    Verificado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    <flux:icon.exclamation-triangle class="w-3.5 h-3.5" />
                                    Pendente
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Painel de Métricas (1 col) ── --}}
                <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 overflow-hidden flex flex-col">
                    <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
                    <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Resumo de Actividade</h3>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Acções totais</span>
                                <span class="text-2xl font-bold tabular-nums text-zinc-900 dark:text-zinc-50">{{ number_format($stats['total_acoes']) }}</span>
                            </div>
                            <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Hoje</span>
                                <span class="text-2xl font-bold tabular-nums {{ $stats['acoes_hoje'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-50' }}">{{ $stats['acoes_hoje'] }}</span>
                            </div>
                            <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Acessos</span>
                                <span class="text-2xl font-bold tabular-nums text-zinc-900 dark:text-zinc-50">{{ number_format($stats['acessos']) }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <p class="text-xs text-zinc-400">Último acesso</p>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mt-0.5">
                                {{ $stats['ultimo_acesso'] ? $stats['ultimo_acesso']->diffForHumans() : 'Sem registo' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══ SECÇÃO INFERIOR: Duas colunas ══ --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                {{-- ── Actividade Recente (3 cols) ────────── --}}
                <div class="lg:col-span-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                <flux:icon.clock class="w-3.5 h-3.5 text-zinc-500" />
                            </div>
                            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Actividade Recente</h3>
                        </div>
                        <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider">Últimas 20</span>
                    </div>

                    <div class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                        @forelse($actividades as $act)
                            @php
                                $rowStyle = match($act->accao) {
                                    'criar'    => ['bg-red-500', 'C'],
                                    'editar'   => ['bg-blue-500', 'E'],
                                    'eliminar' => ['bg-red-500', 'X'],
                                    default    => ['bg-zinc-400', '?'],
                                };
                            @endphp
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30 transition-colors">
                                <span class="w-6 h-6 rounded-md {{ $rowStyle[0] }} flex items-center justify-center text-[10px] font-bold text-white shrink-0">{{ $rowStyle[1] }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-800 dark:text-zinc-200 capitalize font-medium">{{ $act->accao }}</span>
                                        <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">{{ class_basename($act->modelo) }}@if($act->modelo_id)#{{ $act->modelo_id }}@endif</span>
                                    </div>
                                </div>
                                <span class="text-[11px] text-zinc-400 tabular-nums whitespace-nowrap">{{ $act->created_at->format('d/m H:i') }}</span>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <flux:icon.inbox class="w-10 h-10 text-zinc-200 dark:text-zinc-700 mx-auto mb-2" />
                                <p class="text-sm text-zinc-400">Nenhuma actividade registada</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── Coluna Direita: Info + Acessos (2 cols) ── --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Detalhes da Conta --}}
                    <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 overflow-hidden">
                        <div class="flex items-center gap-2 px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="w-6 h-6 rounded-md bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                <flux:icon.finger-print class="w-3.5 h-3.5 text-zinc-500" />
                            </div>
                            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Detalhes da Conta</h3>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-400">Membro desde</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $user->created_at?->translatedFormat('d M Y') }}</span>
                            </div>
                            @if($user->criadoPor)
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400">Registado por</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $user->criadoPor->name }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-400">Estado</span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->activo ? 'text-red-600 dark:text-red-400' : 'text-red-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->activo ? 'bg-red-500' : 'bg-red-500' }}"></span>
                                    {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-400">Verificação</span>
                                @if($user->hasVerifiedEmail())
                                    <span class="text-xs font-medium text-red-600 dark:text-red-400">Verificado</span>
                                @else
                                    <span class="text-xs font-medium text-amber-500">Pendente</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Últimos Acessos --}}
                    <div class="rounded-2xl border border-zinc-200/80 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <flux:icon.arrow-right-end-on-rectangle class="w-3.5 h-3.5 text-zinc-500" />
                                </div>
                                <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Acessos</h3>
                            </div>
                            <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider">Últimos 10</span>
                        </div>

                        <div class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                            @forelse($acessos as $acesso)
                                <div class="px-5 py-2.5 flex items-center justify-between hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30 transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300 truncate">{{ $acesso->accao }}</p>
                                        <p class="text-[11px] text-zinc-400 mt-0.5">
                                            {{ $acesso->recurso ?? '—' }}
                                            @if($acesso->ip_address)
                                                <span class="font-mono ml-1">{{ $acesso->ip_address }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <span class="text-[11px] text-zinc-400 tabular-nums whitespace-nowrap ml-3">{{ $acesso->created_at->format('d/m H:i') }}</span>
                                </div>
                            @empty
                                <div class="py-10 text-center">
                                    <flux:icon.lock-closed class="w-8 h-8 text-zinc-200 dark:text-zinc-700 mx-auto mb-2" />
                                    <p class="text-sm text-zinc-400">Sem registos de acesso</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </x-pages::settings.layout>
</div>
