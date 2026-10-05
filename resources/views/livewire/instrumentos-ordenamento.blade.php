<div class="space-y-6">
    {{-- Navegação e Cabeçalho --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Instrumentos de Ordenamento do Território</span>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                        <flux:icon name="map" class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <flux:heading size="xl">Instrumentos de Ordenamento do Território</flux:heading>
                            <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                {{ $municipio->nome }}
                            </span>
                        </div>
                        <flux:text class="mt-1">
                            Planos municipais, plano director, desenvolvimento rural e foral municipal &bull; Província da Huíla
                        </flux:text>
                    </div>
                </div>

                {{-- Resumo Global --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2 text-center dark:border-zinc-800 dark:bg-zinc-800/50">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</span>
                        <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $totalGeral }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-center dark:border-emerald-800/50 dark:bg-emerald-950/30">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Aprovados</span>
                        <p class="text-lg font-bold text-emerald-800 dark:text-emerald-200">{{ $totalAprovados }}</p>
                    </div>
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-center dark:border-sky-800/50 dark:bg-sky-950/30">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-sky-700 dark:text-sky-300">Em Análise</span>
                        <p class="text-lg font-bold text-sky-800 dark:text-sky-200">{{ $totalSubmetidos }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $pmotTipo = $tipos->firstWhere('slug', 'pmot');
        $outrosTipos = $tipos->where('slug', '!=', 'pmot');
        $statsPmot = $pmotTipo ? ($estatisticas[$pmotTipo->id] ?? null) : null;

        $styles = [
            'pdm' => [
                'icon' => 'document-chart-bar',
                'border' => 'border-blue-200 hover:border-blue-400 dark:border-zinc-800 dark:hover:border-blue-500',
                'bg' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
                'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            ],
            'pmdr' => [
                'icon' => 'globe-americas',
                'border' => 'border-amber-200 hover:border-amber-400 dark:border-zinc-800 dark:hover:border-amber-500',
                'bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            ],
            'foral-municipal' => [
                'icon' => 'document-check',
                'border' => 'border-purple-200 hover:border-purple-400 dark:border-zinc-800 dark:hover:border-purple-500',
                'bg' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300',
                'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
            ],
        ];
    @endphp

    {{-- Destaque Principal: Plano Municipal de Ordenamento do Território (PMOT) --}}
    @if($pmotTipo)
        <div class="relative overflow-hidden rounded-3xl border-2 border-red-200 bg-gradient-to-br from-white via-red-50/20 to-white p-6 shadow-md dark:border-red-900/50 dark:from-zinc-900 dark:via-red-950/10 dark:to-zinc-900">
            <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-red-100/40 blur-2xl dark:bg-red-900/20 pointer-events-none"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-5">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md shadow-red-500/20">
                        <flux:icon name="map" class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-red-600 px-3 py-0.5 text-xs font-bold uppercase tracking-wider text-white">
                                Instrumento Principal
                            </span>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                PMOT
                            </span>
                            @if($statsPmot && $statsPmot['ultimo_ano'])
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Vigência mais recente: {{ $statsPmot['ultimo_ano'] }}
                                </span>
                            @endif
                        </div>

                        <h2 class="mt-2 text-xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $pmotTipo->nome }}
                        </h2>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 max-w-2xl">
                            {{ $pmotTipo->descricao ?: 'Define a estratégia de desenvolvimento territorial do município, o modelo de ocupação do solo, as reservas fundiárias e as redes estruturantes.' }}
                        </p>

                        {{-- Indicadores do PMOT --}}
                        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs font-medium text-zinc-600 dark:text-zinc-300">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                <span>Total: <strong>{{ $statsPmot['total'] ?? 0 }} registos</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span>Aprovados: <strong>{{ $statsPmot['aprovados'] ?? 0 }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                <span>Em Apreciação: <strong>{{ $statsPmot['submetidos'] ?? 0 }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400">
                                <flux:icon name="sparkles" class="h-3.5 w-3.5" />
                                <span>Leitura OCR habilitada</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação para o PMOT --}}
                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $pmotTipo->slug]) }}" wire:navigate class="w-full sm:w-auto">
                        <flux:button variant="subtle" icon="sparkles" class="w-full">
                            Novo PMOT (OCR)
                        </flux:button>
                    </a>
                    <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $pmotTipo->slug]) }}" wire:navigate class="w-full sm:w-auto">
                        <flux:button variant="primary" icon="arrow-right" class="w-full">
                            Gerir PMOTs
                        </flux:button>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Outros Instrumentos de Ordenamento do Território (PDM, PMDR, Foral) --}}
    <div>
        <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">
            Outros Instrumentos de Planeamento Municipal
        </h3>

        <div class="grid gap-5 md:grid-cols-3">
            @forelse($outrosTipos as $tipo)
                @php
                    $cfg = $styles[$tipo->slug] ?? [
                        'icon' => 'document',
                        'border' => 'border-zinc-200 hover:border-zinc-400 dark:border-zinc-800',
                        'bg' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                        'badge' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                    ];
                    $st = $estatisticas[$tipo->id] ?? ['total' => 0, 'aprovados' => 0, 'submetidos' => 0];
                @endphp

                <div class="group flex flex-col justify-between rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-zinc-900 {{ $cfg['border'] }}">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $cfg['bg'] }}">
                                <flux:icon :name="$cfg['icon']" class="h-6 w-6" />
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $cfg['badge'] }}">
                                {{ $st['total'] }} {{ $st['total'] === 1 ? 'registo' : 'registos' }}
                            </span>
                        </div>

                        <h4 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $tipo->nome }}
                        </h4>
                        <p class="mt-1 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $tipo->descricao ?: 'Instrumento de planeamento e gestão territorial.' }}
                        </p>

                        <div class="mt-3 flex items-center gap-3 text-[11px] text-zinc-500 dark:text-zinc-400">
                            <span>Aprovados: <strong class="text-emerald-600">{{ $st['aprovados'] }}</strong></span>
                            <span>&bull;</span>
                            <span>Em análise: <strong class="text-sky-600">{{ $st['submetidos'] }}</strong></span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $tipo->slug]) }}" wire:navigate
                            class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                            + Novo
                        </a>
                        <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipo->slug]) }}" wire:navigate
                            class="flex items-center gap-1 text-xs font-bold text-zinc-800 transition group-hover:gap-1.5 dark:text-zinc-100">
                            Aceder <flux:icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 rounded-2xl border border-dashed border-zinc-300 p-8 text-center text-sm text-zinc-500 dark:border-zinc-800">
                    Nenhum outro tipo de instrumento configurado nesta categoria.
                </div>
            @endforelse
        </div>
    </div>
</div>
