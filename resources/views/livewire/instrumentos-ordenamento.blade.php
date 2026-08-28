<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span>Instrumentos de Ordenamento</span>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <flux:heading size="xl">Instrumentos de Ordenamento</flux:heading>
                    <flux:text class="mt-1">Planos municipais, plano director, desenvolvimento rural e foral municipal - {{ $municipio->nome }}</flux:text>
                </div>
                <div class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    {{ array_sum($contagens) }} {{ array_sum($contagens) === 1 ? 'registo' : 'registos' }}
                </div>
            </div>
        </div>
    </div>

    @php
        $styles = [
            'pmot' => ['icon' => 'map', 'card' => 'hover:border-red-300 dark:hover:border-red-600', 'iconClass' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300'],
            'pdm' => ['icon' => 'document-chart-bar', 'card' => 'hover:border-blue-300 dark:hover:border-blue-600', 'iconClass' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'],
            'pmdr' => ['icon' => 'globe-americas', 'card' => 'hover:border-amber-300 dark:hover:border-amber-600', 'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'],
            'foral-municipal' => ['icon' => 'document-check', 'card' => 'hover:border-purple-300 dark:hover:border-purple-600', 'iconClass' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300'],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2">
        @forelse($tipos as $tipo)
            @php
                $cfg = $styles[$tipo->slug] ?? ['icon' => 'document', 'card' => 'hover:border-zinc-300 dark:hover:border-zinc-600', 'iconClass' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'];
                $count = $contagens[$tipo->id] ?? 0;
            @endphp
            <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipo->slug]) }}" class="group block" wire:navigate>
                <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 {{ $cfg['card'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $cfg['iconClass'] }}">
                                <flux:icon :name="$cfg['icon']" class="h-6 w-6" />
                            </div>
                            <div>
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $tipo->nome }}</div>
                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $tipo->descricao ?: 'Sem descricao registada.' }}</div>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $count }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-sm font-medium text-zinc-700 transition group-hover:gap-2 dark:text-zinc-200">
                        Gerir <flux:icon.arrow-right class="h-4 w-4" />
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center text-sm text-zinc-500 dark:border-zinc-700">
                Nenhum tipo de instrumento configurado para esta categoria.
            </div>
        @endforelse
    </div>
</div>
