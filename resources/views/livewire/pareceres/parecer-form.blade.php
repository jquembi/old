<div>
    @section('page-title', $parecerId ? 'Editar Parecer' : 'Novo Parecer')

    <div class="max-w-3xl">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dados do Parecer</h2>
                </div>
            </div>

            <form wire:submit.prevent="submit" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                        <label for="tipo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo</label>
                        <input type="text" id="tipo" wire:model="tipo"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>

                    <div>
                        <label for="numero_parecer" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Numero do Parecer</label>
                        <input type="text" id="numero_parecer" wire:model="numero_parecer"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('numero_parecer') border-red-400 ring-1 ring-red-400 @enderror">
                        @error('numero_parecer') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="data_parecer" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data do Parecer</label>
                        <input type="date" id="data_parecer" wire:model="data_parecer"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>

                    <div>
                        <label for="departamento" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Departamento</label>
                        <input type="text" id="departamento" wire:model="departamento"
                               class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select id="status" wire:model="status"
                                class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            <option value="emitido">Emitido</option>
                            <option value="arquivado">Arquivado</option>
                            <option value="revogado">Revogado</option>
                        </select>
                    </div>
                </div>

                {{-- Resultado --}}
                <div class="bg-slate-50 dark:bg-slate-700/30 p-4 rounded-xl border border-slate-200 dark:border-slate-700/60">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Resultado do Parecer</h3>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="parecer_favoravel" class="w-4 h-4 text-emerald-600 rounded border-slate-300 dark:border-slate-600 focus:ring-emerald-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Favoravel</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="parecer_desfavoravel" class="w-4 h-4 text-red-600 rounded border-slate-300 dark:border-slate-600 focus:ring-red-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Desfavoravel</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="parecer_condicional" class="w-4 h-4 text-amber-600 rounded border-slate-300 dark:border-slate-600 focus:ring-amber-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Condicional</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descricao</label>
                    <textarea id="descricao" wire:model="descricao" rows="4"
                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                </div>

                <div>
                    <label for="recomendacoes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Recomendacoes</label>
                    <textarea id="recomendacoes" wire:model="recomendacoes" rows="4"
                              class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-200 dark:border-slate-700/60">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                        <span wire:loading.remove><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                        <span wire:loading><svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                        {{ $parecerId ? 'Atualizar' : 'Criar' }}
                    </button>
                    <a href="{{ route('pareceres.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
