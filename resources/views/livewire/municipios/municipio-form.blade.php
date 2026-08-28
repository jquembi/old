<div>
    @section('page-title', $municipioId ? 'Editar Municipio' : 'Novo Municipio')

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-7 0H5m0 0a2 2 0 01-2-2v-4.5M12 9v2m0 4h.01"/></svg>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dados do Municipio</h2>
                </div>
            </div>

            <form wire:submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome <span class="text-red-500">*</span></label>
                    <input type="text" id="nome" wire:model="nome" placeholder="Ex: Lubango"
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('nome') border-red-400 ring-1 ring-red-400 @enderror">
                    @error('nome') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sede" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Sede <span class="text-red-500">*</span></label>
                    <input type="text" id="sede" wire:model="sede" placeholder="Ex: Lubango"
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('sede') border-red-400 ring-1 ring-red-400 @enderror">
                    @error('sede') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="populacao_est" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Populacao Estimada</label>
                    <input type="number" id="populacao_est" wire:model="populacao_est" placeholder="Ex: 876339"
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('populacao_est') border-red-400 ring-1 ring-red-400 @enderror">
                    @error('populacao_est') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-200 dark:border-slate-700/60">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                        <span wire:loading.remove><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                        <span wire:loading><svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                        {{ $municipioId ? 'Atualizar' : 'Criar' }}
                    </button>
                    <a href="{{ route('municipios.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
