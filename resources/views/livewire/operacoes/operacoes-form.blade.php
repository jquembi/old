<div>
    @section('page-title', $operacaoId ? 'Editar Operacao' : 'Nova Operacao')

    <form wire:submit.prevent="submit" class="space-y-6">
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- LEFT COLUMN --}}
            <div class="space-y-6">

                {{-- Card 1: Identificacao --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Identificacao</h3>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome <span class="text-red-500">*</span></label>
                                <input type="text" id="nome" wire:model="nome"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('nome') border-red-400 ring-1 ring-red-400 @enderror">
                                @error('nome') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tipo_operacao" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Operacao</label>
                                <input type="text" id="tipo_operacao" wire:model="tipo_operacao"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
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
                                <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                                <select id="status" wire:model="status"
                                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                                    <option value="planejamento">Planejamento</option>
                                    <option value="em_execucao">Em Execucao</option>
                                    <option value="concluido">Concluido</option>
                                    <option value="suspenso">Suspenso</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Detalhes --}}
                <div x-data="{ open: true }" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Detalhes</h3>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="open" x-collapse class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="area_m2" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Area (m2)</label>
                                <input type="number" id="area_m2" wire:model="area_m2" step="0.01"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="beneficiarios_estimados" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Beneficiarios</label>
                                <input type="number" id="beneficiarios_estimados" wire:model="beneficiarios_estimados"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="orcamento_estimado" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Orcamento (AOA)</label>
                                <input type="number" id="orcamento_estimado" wire:model="orcamento_estimado" step="0.01"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data de Inicio</label>
                                <input type="date" id="data_inicio" wire:model="data_inicio"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="data_fim_prevista" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data Conclusao Prevista</label>
                                <input type="date" id="data_fim_prevista" wire:model="data_fim_prevista"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Descricao & Observacoes --}}
                <div x-data="{ open: true }" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Descricao & Observacoes</h3>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="open" x-collapse class="p-5 space-y-4">
                        <div>
                            <label for="descricao" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Descricao</label>
                            <textarea id="descricao" wire:model="descricao" rows="4"
                                      class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                        </div>
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Observacoes</label>
                            <textarea id="observacoes" wire:model="observacoes" rows="3"
                                      class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-6">

                {{-- Card 4: Resumo --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Resumo</h3>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 text-center">
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Status</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ ucfirst(str_replace('_', ' ', $status)) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 text-center">
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Ficheiros</p>
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ count($existing_files) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 5: Area de Trabalho --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Area de Trabalho</h3>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        {{-- Existing files list --}}
                        @if(count($existing_files) > 0)
                            <div class="space-y-2">
                                @foreach($existing_files as $file)
                                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/30 rounded-lg group">
                                        @php
                                            $mime = $file['tipo_mime'] ?? '';
                                            $iconColor = 'text-slate-400';
                                            if (str_contains($mime, 'pdf')) $iconColor = 'text-red-500';
                                            elseif (str_contains($mime, 'image')) $iconColor = 'text-blue-500';
                                            elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) $iconColor = 'text-blue-600';
                                            elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) $iconColor = 'text-emerald-600';
                                        @endphp
                                        <svg class="w-5 h-5 {{ $iconColor }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate">{{ $file['nome_original'] }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $mime }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ asset('storage/' . $file['caminho']) }}" target="_blank"
                                               class="p-1.5 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition" title="Abrir ficheiro">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <a href="{{ asset('storage/' . $file['caminho']) }}" download
                                               class="p-1.5 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Descarregar">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            <button type="button" wire:click="removeExistingFile({{ $file['id'] }})" wire:confirm="Tem certeza que deseja remover este ficheiro?"
                                                    class="p-1.5 rounded text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition opacity-0 group-hover:opacity-100" title="Remover">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Upload zone --}}
                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-5 text-center hover:border-emerald-400 dark:hover:border-emerald-600 transition">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Arraste ficheiros ou clique para adicionar</p>
                            <input type="file" wire:model="workspace_files" multiple
                                   class="w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-emerald-50 file:text-emerald-600 dark:file:bg-emerald-900/30 dark:file:text-emerald-400 hover:file:bg-emerald-100 cursor-pointer">
                            <p class="mt-2 text-[10px] text-slate-400">PDF, Word, Excel, Imagens - Max 20MB por ficheiro</p>
                        </div>
                        @error('workspace_files.*') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="workspace_files" class="mt-2">
                            <div class="flex items-center gap-2 text-xs text-emerald-600">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                A carregar ficheiros...
                            </div>
                        </div>

                        @if(count($existing_files) === 0)
                            <div class="px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800/40">
                                <p class="text-[10px] text-blue-600 dark:text-blue-400">
                                    <strong>Dica:</strong> Carregue documentos, plantas, fotografias e outros ficheiros relevantes para esta operacao. Os ficheiros ficarao disponiveis na area de trabalho.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-3 border-t border-slate-200 dark:border-slate-700/60">
            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                <span wire:loading><svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                {{ $operacaoId ? 'Atualizar' : 'Criar' }}
            </button>
            <a href="{{ route('operacoes.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
