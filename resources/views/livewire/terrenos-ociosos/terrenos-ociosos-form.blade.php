<div>
    @section('page-title', $terrenoId ? 'Editar Terreno Ocioso' : 'Novo Terreno Ocioso')

    <form wire:submit.prevent="submit" class="space-y-6">
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- LEFT COLUMN --}}
            <div class="space-y-6">

                {{-- Card: Upload do PDF --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Upload do Documento (PDF)</h3>
                            @if(!$terrenoId) <span class="text-[10px] text-red-500 font-medium ml-1">Obrigatorio</span> @endif
                        </div>
                    </div>
                    <div class="p-5">
                        @if($existing_pdf)
                            <div class="mb-4 flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800/40">
                                <svg class="w-8 h-8 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">PDF actual carregado</p>
                                    <p class="text-[10px] text-emerald-600/70 dark:text-emerald-500/70 truncate">{{ basename($existing_pdf) }}</p>
                                </div>
                                <a href="{{ asset('storage/' . $existing_pdf) }}" target="_blank" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 underline">Ver</a>
                            </div>
                        @endif
                        <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-6 text-center hover:border-emerald-400 dark:hover:border-emerald-600 transition">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Arraste o PDF aqui ou clique para seleccionar</p>
                            <input type="file" wire:model="pdf_file" accept=".pdf" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-600 dark:file:bg-emerald-900/30 dark:file:text-emerald-400 hover:file:bg-emerald-100 cursor-pointer">
                            <p class="mt-2 text-[10px] text-slate-400">Maximo 20MB - Apenas PDF</p>
                        </div>
                        @error('pdf_file') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="pdf_file" class="mt-3">
                            <div class="flex items-center gap-2 text-xs text-emerald-600">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                A carregar ficheiro...
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Dados do Proprietario --}}
                <div x-data="{ open: true }" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Dados do Proprietario</h3>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="open" x-collapse class="p-5 space-y-4">
                        <div>
                            <label for="proprietario" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nome <span class="text-red-500">*</span></label>
                            <input type="text" id="proprietario" wire:model="proprietario" placeholder="Nome completo do proprietario"
                                   class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition @error('proprietario') border-red-400 ring-1 ring-red-400 @enderror">
                            @error('proprietario') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="contacto_proprietario" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Contacto</label>
                                <input type="text" id="contacto_proprietario" wire:model="contacto_proprietario" placeholder="Telefone ou e-mail"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="bi_proprietario" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">B.I.</label>
                                <input type="text" id="bi_proprietario" wire:model="bi_proprietario" placeholder="No. do Bilhete de Identidade"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Dados do Terreno --}}
                <div x-data="{ open: true }" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30 cursor-pointer select-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Dados do Terreno</h3>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="open" x-collapse class="p-5 space-y-4">
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
                                    <option value="em_analise">Em Analise</option>
                                    <option value="apto">Apto</option>
                                    <option value="solicitado">Solicitado</option>
                                    <option value="em_desenvolvimento">Em Desenvolvimento</option>
                                    <option value="concluido">Concluido</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="endereco" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Endereco</label>
                                <input type="text" id="endereco" wire:model="endereco"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="lote" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Lote</label>
                                <input type="text" id="lote" wire:model="lote"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="area_m2" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Area (m2)</label>
                                <input type="number" id="area_m2" wire:model="area_m2" step="0.01"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="data_levantamento" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data Levantamento</label>
                                <input type="date" id="data_levantamento" wire:model="data_levantamento"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="motivo_ociosidade" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Motivo Ociosidade</label>
                                <input type="text" id="motivo_ociosidade" wire:model="motivo_ociosidade"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="tempo_ociosidade_anos" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tempo Ociosidade (anos)</label>
                                <input type="number" id="tempo_ociosidade_anos" wire:model="tempo_ociosidade_anos"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="potencial_uso" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Potencial de Uso</label>
                                <input type="text" id="potencial_uso" wire:model="potencial_uso"
                                       class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            </div>
                            <div>
                                <label for="prioridade_intervencao" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Prioridade <span class="text-red-500">*</span></label>
                                <select id="prioridade_intervencao" wire:model="prioridade_intervencao"
                                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                                    <option value="baixa">Baixa</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Observacoes</label>
                            <textarea id="observacoes" wire:model="observacoes" rows="2" placeholder="Observacoes adicionais..."
                                      class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-6">

                {{-- Card: Coordenadas UTM --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Coordenadas UTM</h3>
                                <span class="text-[10px] text-red-500 font-medium">Obrigatorio</span>
                            </div>
                            <button type="button" wire:click="addCoordenadaUtm"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Coordenada
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        @error('coordenadas_utm') <p class="mb-3 text-xs text-red-500">{{ $message }}</p> @enderror

                        <div class="space-y-3">
                            @foreach($coordenadas_utm as $i => $coord)
                                <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-4 relative group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ponto {{ $i + 1 }}</span>
                                        @if(count($coordenadas_utm) > 1)
                                            <button type="button" wire:click="removeCoordenadaUtm({{ $i }})"
                                                    class="p-1 rounded text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition opacity-0 group-hover:opacity-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-1">Easting (X) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="coordenadas_utm.{{ $i }}.easting" step="0.0001" placeholder="500000.0000"
                                                   class="w-full px-2.5 py-2 text-xs tabular-nums bg-white dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-800 dark:text-white focus:ring-1 focus:ring-emerald-500 outline-none @error('coordenadas_utm.'.$i.'.easting') border-red-400 @enderror">
                                            @error('coordenadas_utm.'.$i.'.easting') <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-1">Northing (Y) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="coordenadas_utm.{{ $i }}.northing" step="0.0001" placeholder="8300000.0000"
                                                   class="w-full px-2.5 py-2 text-xs tabular-nums bg-white dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-800 dark:text-white focus:ring-1 focus:ring-emerald-500 outline-none @error('coordenadas_utm.'.$i.'.northing') border-red-400 @enderror">
                                            @error('coordenadas_utm.'.$i.'.northing') <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-1">Zona <span class="text-red-500">*</span></label>
                                            <select wire:model="coordenadas_utm.{{ $i }}.zone"
                                                    class="w-full px-2.5 py-2 text-xs bg-white dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-800 dark:text-white focus:ring-1 focus:ring-emerald-500 outline-none">
                                                <option value="33S">33S</option>
                                                <option value="33N">33N</option>
                                                <option value="34S">34S</option>
                                                <option value="34N">34N</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" wire:model="coordenadas_utm.{{ $i }}.descricao" placeholder="Descricao (ex: Marco NE, Vertice 1...)"
                                               class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded text-slate-800 dark:text-white focus:ring-1 focus:ring-emerald-500 outline-none">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800/40">
                            <p class="text-[10px] text-blue-600 dark:text-blue-400">
                                <strong>Dica:</strong> A zona UTM para Huila/Angola e tipicamente <strong>33S</strong>. Pode adicionar quantas coordenadas forem necessarias.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Resumo --}}
                @if(count($coordenadas_utm) > 0)
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-700/30">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Resumo</h3>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 text-center">
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Pontos</p>
                                    <p class="text-lg font-bold text-slate-800 dark:text-white tabular-nums">{{ count($coordenadas_utm) }}</p>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 text-center">
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Sistema</p>
                                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">UTM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-3 border-t border-slate-200 dark:border-slate-700/60">
            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                <span wire:loading><svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                {{ $terrenoId ? 'Atualizar' : 'Criar' }}
            </button>
            <a href="{{ route('terrenos-ociosos.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
