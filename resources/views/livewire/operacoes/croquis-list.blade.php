<div>
    @section('page-title', 'Croquis')

    <div class="space-y-5">
        {{-- Filters --}}
        <x-ui.filter-bar columns="md:grid-cols-3">
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar proprietário, endereço ou registo..." />
            <x-ui.filter-field aria-label="Filtrar por condição" wire:model.live="condicao">
                <option value="">Todas condicoes</option>
                <option value="legal">Legal</option>
                <option value="ilegal">Ilegal</option>
                <option value="em_conflito">Em Conflito</option>
            </x-ui.filter-field>
            <x-ui.filter-field aria-label="Filtrar por tipo de geometria" wire:model.live="tipo_geometria">
                <option value="">Todos tipos</option>
                <option value="Point">Ponto</option>
                <option value="Polygon">Terreno / Lote</option>
            </x-ui.filter-field>
            <x-slot:trailing>
                <a href="{{ route('croquis.create') }}" class="btn-primary whitespace-nowrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Novo Croqui
                </a>
            </x-slot:trailing>
        </x-ui.filter-bar>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/40 border-b border-slate-200 dark:border-slate-700/60">
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer hover:text-emerald-600 transition" wire:click="sortBy('proprietario')">
                                <span class="inline-flex items-center gap-1">Proprietario
                                    @if($sortBy === 'proprietario')
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $sortDirection === 'asc' ? 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' : 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' }}"/></svg>
                                    @endif
                                </span>
                            </th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Condicao</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Municipio</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Area (m²)</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($croquis as $croqui)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('croquis.show', $croqui) }}" class="font-medium text-slate-800 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition">{{ $croqui->proprietario }}</a>
                                    @if($croqui->numero_registro)
                                        <div class="text-xs text-slate-400">{{ $croqui->numero_registro }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium
                                        {{ $croqui->tipo_geometria === 'Point' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' }}">
                                        {{ $croqui->tipo_geometria === 'Point' ? 'Ponto' : 'Terreno' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($croqui->condicao === 'legal') bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                        @elseif($croqui->condicao === 'ilegal') bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            @if($croqui->condicao === 'legal') bg-emerald-500
                                            @elseif($croqui->condicao === 'ilegal') bg-red-500
                                            @else bg-amber-500 @endif"></span>
                                        {{ ucfirst(str_replace('_', ' ', $croqui->condicao)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $croqui->municipio->nome }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600 dark:text-slate-300 tabular-nums">
                                    @if($croqui->area_calculada_m2)
                                        {{ number_format($croqui->area_calculada_m2, 1, ',', '.') }}
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('croquis.show', $croqui) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition" title="Ver detalhes">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('croquis.edit', $croqui) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if($this->podeEditar($croqui))
                                            <button type="button"
                                                    onclick='window.dispatchEvent(new CustomEvent("confirmar", { detail: { title: "Eliminar croqui", message: "Tem certeza que deseja eliminar este croqui?", confirmLabel: "Eliminar", variant: "danger", action: "deleteCroqui", params: [{{ (int) $croqui->id }}], component: @js($this->getId()) } }))'
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Deletar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum croqui encontrado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($croquis->hasPages())
            <div class="flex justify-center">{{ $croquis->links() }}</div>
        @endif
    </div>
</div>
