<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span>Projectos de Requalificacao</span>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="xl">Projectos de Requalificacao</flux:heading>
            <flux:text class="mt-1">Acompanhamento de iniciativas SONA, requalificacao urbana e infraestrutura - {{ $municipio->nome }}</flux:text>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <a href="{{ route('municipio.requalificacao.sona', $municipio) }}" class="group block" wire:navigate>
            <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-teal-600">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300">
                            <flux:icon.building-office class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">Projecto SONA</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Requalificacao urbana, fases, documentos e acompanhamento tecnico.</div>
                        </div>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $stats['sona'] }}</span>
                </div>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-teal-700 transition group-hover:gap-2 dark:text-teal-300">
                    Gerir SONA <flux:icon.arrow-right class="h-4 w-4" />
                </div>
            </div>
        </a>

        <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" class="group block" wire:navigate>
            <div class="h-full rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-cyan-600">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">
                            <flux:icon.clipboard-document-list class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">Outros Projectos</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Projectos de requalificacao, infraestrutura e intervencoes sociais.</div>
                        </div>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $stats['outros'] }}</span>
                </div>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-cyan-700 transition group-hover:gap-2 dark:text-cyan-300">
                    Gerir projectos <flux:icon.arrow-right class="h-4 w-4" />
                </div>
            </div>
        </a>
    </div>
</div>
