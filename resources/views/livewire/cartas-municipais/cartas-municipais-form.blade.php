<div>
    @section('page-title', $cartaId ? 'Editar Carta Municipal' : 'Nova Carta Municipal')

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dados da Carta Municipal</h2>
                </div>
            </div>

            <form wire:submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome <span class="text-red-500">*</span></label>
                    <input type="text" id="nome" wire:model="nome"
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('nome') border-red-400 ring-1 ring-red-400 @enderror">
                    @error('nome') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tipo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                    <input type="text" id="tipo" wire:model="tipo"
                           class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('tipo') border-red-400 ring-1 ring-red-400 @enderror">
                    @error('tipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="municipio_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Municipio <span class="text-red-500">*</span></label>
                    <select id="municipio_id" wire:model="municipio_id"
                            class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('municipio_id') border-red-400 ring-1 ring-red-400 @enderror">
                        <option value="">Selecione um municipio</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                        @endforeach
                    </select>
                    @error('municipio_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descricao</label>
                    <textarea id="descricao" wire:model="descricao" rows="4"
                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none @error('descricao') border-red-400 ring-1 ring-red-400 @enderror"></textarea>
                    @error('descricao') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="escala" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Escala</label>
                        <input type="text" id="escala" wire:model="escala" placeholder="Ex: 1:50000"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select id="status" wire:model="status"
                                class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            <option value="ativa">Ativa</option>
                            <option value="inativa">Inativa</option>
                            <option value="arquivada">Arquivada</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-200 dark:border-slate-700/60">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                        <span wire:loading.remove><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                        <span wire:loading><svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                        {{ $cartaId ? 'Atualizar' : 'Criar' }}
                    </button>
                    <a href="{{ route('cartas-municipais.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
