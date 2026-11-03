<div>
    @section('page-title', 'Pareceres')

    <div class="space-y-5">
        <x-ui.filter-bar columns="md:grid-cols-3">
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por número..." />
            <x-ui.filter-field aria-label="Filtrar por município" wire:model.live="municipio_id">
                <option value="">Todos municipios</option>
                @foreach($municipios as $municipio)
                    <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                @endforeach
            </x-ui.filter-field>
            <x-ui.filter-field aria-label="Filtrar por tipo" wire:model.live="tipo">
                <option value="">Todos tipos</option>
                @foreach($tipos ?? [] as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </x-ui.filter-field>
            <x-slot:trailing>
                <a href="{{ route('pareceres.create') }}" class="btn-primary whitespace-nowrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Novo Parecer
                </a>
            </x-slot:trailing>
        </x-ui.filter-bar>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/40 border-b border-slate-200 dark:border-slate-700/60">
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Numero</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Municipio</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Resultado</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($pareceres as $parecer)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-white">{{ $parecer->numero_parecer }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $parecer->tipo }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $parecer->municipio->nome ?? '-' }}</td>
                                <td class="px-5 py-3.5">
                                    @if($parecer->parecer_favoravel)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Favoravel</span>
                                    @elseif($parecer->parecer_desfavoravel)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Desfavoravel</span>
                                    @elseif($parecer->parecer_condicional)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Condicional</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ ucfirst($parecer->status) }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('pareceres.edit', $parecer) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button wire:click="delete({{ $parecer->id }})" wire:confirm="Tem certeza que deseja deletar este parecer?"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Deletar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum parecer encontrado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($pareceres->hasPages())
            <div class="flex justify-center">{{ $pareceres->links() }}</div>
        @endif
    </div>
</div>
