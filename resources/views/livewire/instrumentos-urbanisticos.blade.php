<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span>Instrumentos Urbanisticos</span>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <flux:heading size="xl">Instrumentos Urbanisticos</flux:heading>
                    <flux:text class="mt-1">Plano urbano, plano de pormenor, requalificacao, loteamentos e regulamentos - {{ $municipio->nome }}</flux:text>
                </div>
                <div class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    {{ array_sum($contagens) + $loteamentos['total'] }} {{ (array_sum($contagens) + $loteamentos['total']) === 1 ? 'registo' : 'registos' }}
                </div>
            </div>
        </div>
    </div>

    @php
        $styles = [
            'pum' => ['icon' => 'building-office', 'card' => 'hover:border-blue-300 dark:hover:border-blue-600', 'iconClass' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'],
            'plano-pormenor' => ['icon' => 'magnifying-glass', 'card' => 'hover:border-indigo-300 dark:hover:border-indigo-600', 'iconClass' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'],
            'requalificacao-urbana' => ['icon' => 'sparkles', 'card' => 'hover:border-amber-300 dark:hover:border-amber-600', 'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'],
            'regulamentos-urbanisticos' => ['icon' => 'clipboard-document-list', 'card' => 'hover:border-purple-300 dark:hover:border-purple-600', 'iconClass' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300'],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2">
        @foreach($tipos as $tipo)
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
        @endforeach

        <a href="{{ route('municipio.loteamentos', $municipio) }}" class="group block" wire:navigate>
            <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-emerald-600">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                            <flux:icon.squares-2x2 class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">Projectos de Loteamentos</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Acompanhamento de loteamentos aprovados, concluidos e em curso.</div>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">{{ $loteamentos['aprovados'] }} aprovados</span>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ $loteamentos['em_curso'] }} em curso</span>
                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $loteamentos['concluidos'] }} concluidos</span>
                            </div>
                        </div>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $loteamentos['total'] }}</span>
                </div>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-emerald-700 transition group-hover:gap-2 dark:text-emerald-300">
                    Gerir <flux:icon.arrow-right class="h-4 w-4" />
                </div>
            </div>
        </a>
    </div>
</div>
