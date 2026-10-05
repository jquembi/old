<div class="space-y-6 px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
    <div>
        <nav class="mb-3 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Caminho de navegacao">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipios.index') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Municipios</a>
            <span>/</span>
            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $municipio->nome }}</span>
        </nav>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                        <flux:icon.map-pin class="h-6 w-6" />
                    </div>
                    <div>
                        <flux:heading size="xl">{{ $municipio->nome }}</flux:heading>
                        <flux:text class="mt-1">Centro de gestao territorial, instrumentos, operacoes e projectos.</flux:text>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 w-full lg:w-auto">
                    @php
                        $statsItems = [
                            ['label' => 'Instrumentos', 'value' => $stats['instrumentos']],
                            ['label' => 'Loteamentos', 'value' => $stats['loteamentos']],
                            ['label' => 'Croquis', 'value' => $stats['croquis']],
                            ['label' => 'Pareceres', 'value' => $stats['pareceres']],
                            ['label' => 'SONA', 'value' => $stats['sona']],
                            ['label' => 'Outros', 'value' => $stats['outros']],
                        ];
                    @endphp
                    @foreach($statsItems as $item)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-800">
                            <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $item['value'] }}</div>
                            <div class="text-[10px] uppercase tracking-wide text-zinc-500">{{ $item['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @php
        $modules = [
            [
                'title' => 'Instrumentos de Ordenamento',
                'desc' => 'Planos Municipais, Plano Director, Desenvolvimento Rural e Foral Municipal.',
                'route' => route('municipio.instrumentos-ordenamento', $municipio),
                'icon' => 'map',
                'count' => $stats['instrumentos'],
                'iconClass' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
                'card' => 'hover:border-red-300 dark:hover:border-red-600',
            ],
            [
                'title' => 'Instrumentos Urbanisticos',
                'desc' => 'Plano Urbano, Plano de Pormenor, Requalificacao, Loteamentos e Regulamentos.',
                'route' => route('municipio.instrumentos-urbanisticos', $municipio),
                'icon' => 'building-office',
                'count' => $stats['loteamentos'],
                'iconClass' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
                'card' => 'hover:border-blue-300 dark:hover:border-blue-600',
            ],
            [
                'title' => 'Cartas Municipais',
                'desc' => 'Cartas e mapas municipais com pesquisa, consulta e download de ficheiros.',
                'route' => route('municipio.cartas', $municipio),
                'icon' => 'document-text',
                'count' => $stats['instrumentos'],
                'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                'card' => 'hover:border-amber-300 dark:hover:border-amber-600',
            ],
            [
                'title' => 'Operacoes Urbanisticas',
                'desc' => 'Croquis elaborados, terrenos ociosos, pareceres do INOTU e ambiente de trabalho.',
                'route' => route('municipio.operacoes', $municipio),
                'icon' => 'folder-open',
                'count' => $stats['croquis'] + $stats['pareceres'],
                'iconClass' => 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300',
                'card' => 'hover:border-violet-300 dark:hover:border-violet-600',
            ],
            [
                'title' => 'Projectos de Requalificacao',
                'desc' => 'Projecto SONA e outros projectos de requalificacao e infraestrutura.',
                'route' => route('municipio.requalificacao', $municipio),
                'icon' => 'clipboard-document-list',
                'count' => $stats['sona'] + $stats['outros'],
                'iconClass' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300',
                'card' => 'hover:border-teal-300 dark:hover:border-teal-600',
            ],
        ];
    @endphp

    <div>
        <div class="mb-3 flex items-center justify-between">
            <div class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Modulos</div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module)
                <a href="{{ $module['route'] }}" class="group block" wire:navigate>
                    <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 {{ $module['card'] }}">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                            <div class="flex gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $module['iconClass'] }}">
                                    <flux:icon :name="$module['icon']" class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $module['title'] }}</div>
                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $module['desc'] }}</div>
                                </div>
                            </div>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $module['count'] }}</span>
                        </div>
                        <div class="mt-4 flex items-center gap-1 text-sm font-medium text-zinc-700 transition group-hover:gap-2 dark:text-zinc-200">
                            Aceder <flux:icon.arrow-right class="h-4 w-4" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
