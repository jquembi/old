<div>
    @section('page-title', 'Instrumentos de Ordenamento')

    <div class="space-y-5">
        {{-- Filters --}}
        <x-ui.filter-bar columns="md:grid-cols-2">
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Título do instrumento..." />
            <x-ui.filter-field aria-label="Filtrar por tipo" wire:model.live="tipo">
                <option value="">Todos tipos</option>
                <option value="pmot">PMOT</option>
                <option value="pdm">PDM</option>
                <option value="pmdr">PMDR</option>
                <option value="foral">Foral</option>
            </x-ui.filter-field>
            <x-slot:trailing>
                <a href="{{ route('instrumentos-ordenamento.create') }}" class="btn-primary whitespace-nowrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Novo Instrumento
                </a>
            </x-slot:trailing>
        </x-ui.filter-bar>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/40 border-b border-slate-200 dark:border-slate-700/60">
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Titulo</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Municipio</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($instrumentos as $instrumento)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-white">{{ $instrumento->titulo }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        {{ strtoupper($instrumento->tipo) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $instrumento->municipio->nome }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('instrumentos-ordenamento.edit', $instrumento) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if($podeEditar($instrumento))
                                            <button type="button"
                                                    onclick='window.dispatchEvent(new CustomEvent("confirmar", { detail: { title: "Eliminar instrumento", message: "Tem certeza que deseja eliminar este instrumento?", confirmLabel: "Eliminar", variant: "danger", action: "deleteInstrumento", params: [{{ (int) $instrumento->id }}], component: @js($this->getId()) } }))'
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Deletar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum instrumento encontrado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($instrumentos->hasPages())
            <div class="flex justify-center">{{ $instrumentos->links() }}</div>
        @endif
    </div>
</div>
