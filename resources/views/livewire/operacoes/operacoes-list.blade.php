<div>
    @section('page-title', 'Operacoes')

    <div class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 p-5">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Buscar operacoes..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
                <select wire:model.live="municipio_id"
                        class="w-full sm:w-48 py-2.5 px-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    <option value="">Todos municipios</option>
                    @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status"
                        class="w-full sm:w-44 py-2.5 px-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    <option value="">Todos status</option>
                    <option value="planejamento">Planejamento</option>
                    <option value="em_execucao">Em Execucao</option>
                    <option value="concluido">Concluido</option>
                    <option value="suspenso">Suspenso</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <a href="{{ route('operacoes.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nova Operacao
                </a>
            </div>
        </div>

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
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Municipio</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Orcamento</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($operacoes as $operacao)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-white">{{ $operacao->nome }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $operacao->tipo_operacao ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">{{ $operacao->municipio->nome ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-right text-slate-600 dark:text-slate-300 tabular-nums">AOA {{ number_format($operacao->orcamento ?? 0, 2, ',', '.') }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $statusMap = [
                                            'concluido' => ['bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'bg-emerald-500'],
                                            'cancelado' => ['bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400', 'bg-red-500'],
                                            'suspenso' => ['bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', 'bg-amber-500'],
                                            'em_execucao' => ['bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'bg-blue-500'],
                                        ];
                                        $s = $statusMap[$operacao->status] ?? ['bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'bg-slate-400'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $s[0] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $s[1] }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $operacao->status)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('operacoes.edit', $operacao) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <button wire:click="delete({{ $operacao->id }})" wire:confirm="Tem certeza que deseja deletar esta operacao?"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Deletar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhuma operacao encontrada</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($operacoes->hasPages())
            <div class="flex justify-center">{{ $operacoes->links() }}</div>
        @endif
    </div>
</div>
