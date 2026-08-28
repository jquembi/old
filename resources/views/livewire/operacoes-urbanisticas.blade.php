<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span>Operacoes Urbanisticas</span>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <flux:heading size="xl">Operacoes Urbanisticas</flux:heading>
                    <flux:text class="mt-1">Croquis elaborados, terrenos ociosos, pareceres e documentos tecnicos do INOTU - {{ $municipio->nome }}</flux:text>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                    <div class="rounded-lg bg-violet-50 px-3 py-2 text-center dark:bg-violet-950/30">
                        <div class="text-lg font-semibold text-violet-700 dark:text-violet-300">{{ $stats['croquis_elaborados'] }}</div>
                        <div class="text-[11px] uppercase text-violet-500">Croquis</div>
                    </div>
                    <div class="rounded-lg bg-amber-50 px-3 py-2 text-center dark:bg-amber-950/30">
                        <div class="text-lg font-semibold text-amber-700 dark:text-amber-300">{{ $stats['terrenos_ociosos'] }}</div>
                        <div class="text-[11px] uppercase text-amber-500">Terrenos</div>
                    </div>
                    <div class="rounded-lg bg-rose-50 px-3 py-2 text-center dark:bg-rose-950/30">
                        <div class="text-lg font-semibold text-rose-700 dark:text-rose-300">{{ $stats['pareceres'] }}</div>
                        <div class="text-[11px] uppercase text-rose-500">Pareceres</div>
                    </div>
                    <div class="rounded-lg bg-sky-50 px-3 py-2 text-center dark:bg-sky-950/30">
                        <div class="text-lg font-semibold text-sky-700 dark:text-sky-300">{{ $stats['arquivos_trabalho'] }}</div>
                        <div class="text-[11px] uppercase text-sky-500">Trabalho</div>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-3 py-2 text-center dark:bg-emerald-950/30">
                        <div class="text-lg font-semibold text-emerald-700 dark:text-emerald-300">{{ $stats['ids_territoriais'] }}</div>
                        <div class="text-[11px] uppercase text-emerald-500">IDs Terreno</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $items = [
            [
                'title' => 'IDs de Terreno',
                'desc' => 'Consulta reservada por pesquisa e verificacao de codigo territorial.',
                'count' => $stats['ids_territoriais'],
                'route' => route('municipio.operacoes.ids-terreno', $municipio),
                'icon' => 'shield-check',
                'card' => 'hover:border-emerald-300 dark:hover:border-emerald-600',
                'iconClass' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
            ],
            [
                'title' => 'Croquis Elaborados',
                'desc' => 'Croquis de localizacao, memoria descritiva, coordenadas e publicacao controlada.',
                'count' => $stats['croquis_elaborados'],
                'route' => route('municipio.operacoes.croquis-elaborados', $municipio),
                'icon' => 'map-pin',
                'card' => 'hover:border-violet-300 dark:hover:border-violet-600',
                'iconClass' => 'bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300',
            ],
            [
                'title' => 'Terrenos Ociosos',
                'desc' => 'Registo de terrenos, proprietarios, UTM e documentos PDF associados.',
                'count' => $stats['terrenos_ociosos'],
                'route' => route('municipio.operacoes.terrenos-ociosos', $municipio),
                'icon' => 'globe-alt',
                'card' => 'hover:border-amber-300 dark:hover:border-amber-600',
                'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
            ],
            [
                'title' => 'Pareceres INOTU',
                'desc' => 'Pareceres tecnicos vinculados a recursos, projectos e instrumentos.',
                'count' => $stats['pareceres'],
                'route' => route('municipio.operacoes.pareceres', $municipio),
                'icon' => 'document-check',
                'card' => 'hover:border-rose-300 dark:hover:border-rose-600',
                'iconClass' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
            ],
            [
                'title' => 'Ambiente de Trabalho',
                'desc' => 'Ficheiros QGIS, DWG, bases e material tecnico para colaboracao.',
                'count' => $stats['arquivos_trabalho'],
                'route' => route('municipio.operacoes.ambiente-trabalho', $municipio),
                'icon' => 'folder-open',
                'card' => 'hover:border-sky-300 dark:hover:border-sky-600',
                'iconClass' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
            ],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2">
        @foreach($items as $item)
            <a href="{{ $item['route'] }}" class="group block" wire:navigate>
                <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 {{ $item['card'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $item['iconClass'] }}">
                                <flux:icon :name="$item['icon']" class="h-6 w-6" />
                            </div>
                            <div>
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $item['title'] }}</div>
                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $item['desc'] }}</div>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $item['count'] }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-sm font-medium text-zinc-700 transition group-hover:gap-2 dark:text-zinc-200">
                        Aceder <flux:icon.arrow-right class="h-4 w-4" />
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
