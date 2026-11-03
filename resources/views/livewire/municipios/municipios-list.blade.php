<div>
    @section('page-title', 'Municipios')

    <div class="space-y-5">
        {{-- Search + Action --}}
        <x-ui.filter-bar columns="md:grid-cols-1">
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou sede..." />
            <x-slot:trailing>
                <a href="{{ route('municipios.create') }}" class="btn-primary whitespace-nowrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Novo Municipio
                </a>
            </x-slot:trailing>
        </x-ui.filter-bar>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/40 border-b border-slate-200 dark:border-slate-700/60">
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:text-emerald-600 transition" wire:click="sortBy('nome')">
                                <span class="inline-flex items-center gap-1">Nome
                                    @if($sortBy === 'nome')
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $sortDirection === 'asc' ? 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' : 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' }}"/></svg>
                                    @endif
                                </span>
                            </th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Sede</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Populacao</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($municipios as $municipio)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-white">{{ $municipio->nome }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $municipio->sede }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 tabular-nums">{{ number_format($municipio->populacao_est ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <a href="{{ route('municipios.edit', $municipio) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition inline-flex" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-7 0H5m0 0a2 2 0 01-2-2v-4.5M12 9v2m0 4h.01"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum municipio encontrado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($municipios->hasPages())
            <div class="flex justify-center">{{ $municipios->links() }}</div>
        @endif
    </div>
</div>
