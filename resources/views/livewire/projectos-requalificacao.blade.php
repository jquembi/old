<div class="space-y-6">
    {{-- Cabeçalho e Navegação --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Projectos de Requalificação</span>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-gradient-to-r from-teal-500/10 via-cyan-500/5 to-transparent p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                        <flux:icon.building-office class="h-6 w-6" />
                    </div>
                    <div>
                        <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-zinc-100">Projectos de Requalificação</flux:heading>
                        <flux:text class="mt-0.5 text-zinc-600 dark:text-zinc-400">
                            Acompanhamento integrado de iniciativas SONA, requalificação urbana, infraestruturas e obras — {{ $municipio->nome }}
                        </flux:text>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($podeCriarSona)
                    <a href="{{ route('municipio.requalificacao.sona.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus" size="sm">Novo Projecto SONA</flux:button>
                    </a>
                @endif
                @if($podeCriarOutros)
                    <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="subtle" icon="plus" size="sm">Novo Outro Projecto</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Indicadores Globais / Métricas --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total de Projectos</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400">
                    <flux:icon.clipboard-document-list class="h-4 w-4" />
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_geral'] }}</span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">registados</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Em Curso</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <flux:icon.arrow-path class="h-4 w-4" />
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_em_curso'] }}</span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">em execução</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Concluídos</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <flux:icon.check-circle class="h-4 w-4" />
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['total_concluidos'] }}</span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">finalizados</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Orçamento Estimado</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <flux:icon.banknotes class="h-4 w-4" />
                </span>
            </div>
            <div class="mt-2">
                <span class="text-lg font-bold text-zinc-900 dark:text-white">
                    @if($stats['orcamento_total_outros'] > 0)
                        {{ number_format($stats['orcamento_total_outros'], 2, ',', '.') }} <span class="text-xs font-normal text-zinc-500">AOA</span>
                    @else
                        <span class="text-sm font-medium text-zinc-400">Não informado</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Módulos de Gestão --}}
    <div class="grid gap-6 md:grid-cols-2">
        {{-- Card Projecto SONA --}}
        <div class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-teal-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-teal-600">
            <div>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/50 dark:text-teal-400">
                            <flux:icon.building-office class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Projecto SONA</h2>
                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-bold text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                                    {{ $stats['sona'] }} {{ $stats['sona'] === 1 ? 'projecto' : 'projectos' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                Planos de requalificação urbana, fases executivas, relatórios de avanço de obras e registo fotográfico georreferenciado.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Mini Estatísticas SONA --}}
                <div class="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-zinc-50 p-3 text-center dark:bg-zinc-800/60">
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Requalificação</div>
                        <div class="text-base font-bold text-zinc-800 dark:text-zinc-200">{{ $stats['sona'] - $stats['sona_avancos'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Avanços de Obras</div>
                        <div class="text-base font-bold text-teal-600 dark:text-teal-400">{{ $stats['sona_avancos'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Em Curso</div>
                        <div class="text-base font-bold text-blue-600 dark:text-blue-400">{{ $stats['sona_em_curso'] }}</div>
                    </div>
                </div>

                {{-- Últimos Registos SONA --}}
                @if($ultimosSona->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Últimos Registos</div>
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($ultimosSona as $sona)
                                <div class="py-1.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[200px]">{{ $sona->titulo }}</span>
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ $sona->created_at->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                @if($podeCriarSona)
                    <a href="{{ route('municipio.requalificacao.sona.criar', $municipio) }}" wire:navigate class="text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200">
                        + Adicionar Projecto
                    </a>
                @else
                    <span></span>
                @endif
                <a href="{{ route('municipio.requalificacao.sona', $municipio) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300" wire:navigate>
                    Aceder aos Projectos SONA <flux:icon.arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>

        {{-- Card Outros Projectos --}}
        <div class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-cyan-400 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-cyan-600">
            <div>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400">
                            <flux:icon.clipboard-document-list class="h-7 w-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Outros Projectos</h2>
                                <span class="rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-bold text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    {{ $stats['outros'] }} {{ $stats['outros'] === 1 ? 'projecto' : 'projectos' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                Projectos municipais de requalificação, infraestruturas viárias, equipamentos sociais e intervenções comunitárias.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Mini Estatísticas Outros Projectos --}}
                <div class="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-zinc-50 p-3 text-center dark:bg-zinc-800/60">
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Requalificação</div>
                        <div class="text-base font-bold text-zinc-800 dark:text-zinc-200">{{ $stats['outros_requalificacao'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Infraestrutura</div>
                        <div class="text-base font-bold text-cyan-600 dark:text-cyan-400">{{ $stats['outros_infraestrutura'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Em Execução</div>
                        <div class="text-base font-bold text-blue-600 dark:text-blue-400">{{ $stats['outros_em_curso'] }}</div>
                    </div>
                </div>

                {{-- Últimos Registos Outros Projectos --}}
                @if($ultimosOutros->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Últimos Registos</div>
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($ultimosOutros as $outro)
                                <div class="py-1.5 flex items-center justify-between text-xs">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[200px]">{{ $outro->titulo }}</span>
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ $outro->created_at->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                @if($podeCriarOutros)
                    <a href="{{ route('municipio.requalificacao.outros.criar', $municipio) }}" wire:navigate class="text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200">
                        + Adicionar Projecto
                    </a>
                @else
                    <span></span>
                @endif
                <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300" wire:navigate>
                    Aceder a Outros Projectos <flux:icon.arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>
    </div>
</div>
