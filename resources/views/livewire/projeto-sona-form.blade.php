<div class="space-y-6">
    {{-- Cabeçalho e Navegação --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.requalificacao.sona', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Projectos SONA</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $registoId ? 'Editar Projecto' : 'Novo Projecto' }}</span>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $registoId ? 'Editar Projecto SONA' : 'Novo Projecto SONA' }}
                </flux:heading>
                <flux:text class="mt-0.5 text-zinc-600 dark:text-zinc-400">
                    Preencha as informações técnicas, fases de execução e relatórios de obras — {{ $municipio->nome }}
                </flux:text>
            </div>
            <a href="{{ route('municipio.requalificacao.sona', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left" size="sm">Voltar à Lista</flux:button>
            </a>
        </div>
    </div>

    {{-- Layout do Formulário com Assistente OCR Lateral --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Coluna Principal: Formulário --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <flux:heading size="lg" class="border-b border-zinc-100 pb-3 font-semibold dark:border-zinc-800">
                    Dados do Projecto
                </flux:heading>

                {{-- Título --}}
                <div>
                    <flux:input wire:model="titulo" label="Título do Projecto" placeholder="Ex: Projecto de Requalificação da Frente Marítima / Urbana" required />
                </div>

                {{-- Tipo e Fase --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="tipo" label="Tipo de Projecto">
                        <flux:select.option value="requalificacao">Requalificação Urbana</flux:select.option>
                        <flux:select.option value="avancos_obras">Avanços de Obras</flux:select.option>
                    </flux:select>

                    <div>
                        <flux:input wire:model="fase" label="Fase Actual" placeholder="Ex: Fase 1" />
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="text-[11px] text-zinc-400 self-center mr-1">Atalhos:</span>
                            @foreach(['Fase 1', 'Fase 2', 'Fase 3', 'Fase 4', 'Conclusão'] as $faseOpcao)
                                <button type="button" wire:click="definirFase('{{ $faseOpcao }}')"
                                        class="rounded-md border px-2 py-0.5 text-xs font-medium transition {{ $fase === $faseOpcao ? 'border-teal-500 bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $faseOpcao }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Estado --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="estado" label="Estado de Execução">
                        <flux:select.option value="em_curso">Em Curso</flux:select.option>
                        <flux:select.option value="concluido">Concluído</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>
                </div>

                {{-- Descrição --}}
                <div>
                    <flux:textarea wire:model="descricao" label="Descrição / Memória Resumida" rows="4"
                                   placeholder="Resumo técnico do projecto, âmbito das intervenções, metas operacionais e especificações relevantes." />
                </div>
            </div>

            {{-- Gestão de Anexos e Ficheiros --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <flux:heading size="lg" class="font-semibold">
                        Ficheiros e Documentos Anexos
                    </flux:heading>
                    <span class="text-xs text-zinc-400">PDF, Imagens (JPG, PNG), DOCX até 50MB</span>
                </div>

                {{-- Upload de Novos Ficheiros --}}
                <div class="space-y-3">
                    <div class="rounded-xl border-2 border-dashed border-zinc-300 p-6 text-center transition hover:border-teal-400 dark:border-zinc-700 dark:hover:border-teal-600">
                        <flux:icon.arrow-up-tray class="mx-auto h-8 w-8 text-zinc-400" />
                        <label class="mt-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                            <span class="text-teal-600 dark:text-teal-400 underline">Clique para selecionar</span> ou arraste múltiplos ficheiros
                            <input type="file" wire:model="ficheiros" multiple class="hidden" />
                        </label>
                        <p class="text-xs text-zinc-500 mt-1">Pode enviar relatórios técnicos, fotografias do local ou plantas de projecto.</p>
                    </div>

                    {{-- Indicador de carregamento --}}
                    <div wire:loading wire:target="ficheiros" class="rounded-lg bg-teal-50 p-3 text-xs text-teal-800 dark:bg-teal-950/40 dark:text-teal-300">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span>A processar ficheiros selecionados...</span>
                        </div>
                    </div>

                    {{-- Lista de Novos Ficheiros a Enviar --}}
                    @if(count($ficheiros) > 0)
                        <div class="space-y-3 pt-2">
                            <div class="text-xs font-semibold uppercase text-zinc-500 tracking-wider">
                                Ficheiros Prontos para Gravar ({{ count($ficheiros) }})
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($ficheiros as $index => $novoFicheiro)
                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <flux:icon.paper-clip class="h-4 w-4 shrink-0 text-teal-600" />
                                            <span class="truncate font-medium text-zinc-800 dark:text-zinc-200">{{ $novoFicheiro->getClientOriginalName() }}</span>
                                        </div>
                                        <button type="button" wire:click="removerFicheiroNovo({{ $index }})" class="text-red-500 hover:text-red-700 p-1 shrink-0" title="Remover">
                                            <flux:icon.trash class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Metadados opcionais para o lote de ficheiros --}}
                            <div class="grid gap-3 pt-2 sm:grid-cols-2">
                                <flux:input wire:model="faseFicheiro" label="Fase Associada aos Ficheiros" placeholder="Ex: {{ $fase ?: 'Fase 1' }}" />
                                <flux:input wire:model="legenda" label="Legenda / Descrição dos Ficheiros" placeholder="Ex: Vista panorâmica da intervenção" />
                            </div>
                        </div>
                    @endif

                    {{-- Ficheiros Existentes (Modo de Edição) --}}
                    @if($registoId && count($ficheirosExistentes) > 0)
                        <div class="space-y-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs font-semibold uppercase text-zinc-500 tracking-wider">
                                Ficheiros Já Guardados ({{ count($ficheirosExistentes) }})
                            </div>
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($ficheirosExistentes as $fExistente)
                                    <div class="flex items-center justify-between py-2 text-xs">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <flux:icon.document-check class="h-4 w-4 text-emerald-600 shrink-0" />
                                            <a href="{{ route('storage.serve', $fExistente['caminho']) }}" target="_blank" class="truncate font-medium text-zinc-800 hover:underline dark:text-zinc-200">
                                                {{ $fExistente['nome_original'] }}
                                            </a>
                                            @if(!empty($fExistente['fase']))
                                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $fExistente['fase'] }}</span>
                                            @endif
                                        </div>
                                        <button type="button"
                                                x-on:click.prevent="$dispatch('confirmar', { title: 'Eliminar Ficheiro', message: 'Deseja remover este ficheiro?', action: 'eliminarFicheiroExistente', component: $wire.$id, params: [{{ $fExistente['id'] }}], variant: 'danger' })"
                                                class="text-red-500 hover:text-red-700 p-1">
                                            <flux:icon.trash class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Erros de Validação --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30">
                    <div class="flex items-start gap-2">
                        <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">Por favor, corrija os erros abaixo:</h4>
                            <ul class="mt-1 list-disc list-inside text-xs text-red-700 dark:text-red-400 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botões de Acção --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('municipio.requalificacao.sona', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,ficheiros,documentoAnalise">
                    <span wire:loading.remove wire:target="guardar">{{ $registoId ? 'Actualizar Projecto' : 'Guardar Projecto' }}</span>
                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A processar...
                    </span>
                </flux:button>
            </div>
        </div>

        {{-- Coluna Lateral: Assistente de Leitura OCR e Extração Inteligente --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-teal-200 bg-gradient-to-b from-teal-500/10 via-teal-500/5 to-transparent p-6 shadow-sm dark:border-teal-900/50 dark:bg-zinc-900">
                <div class="flex items-center gap-2 text-teal-800 dark:text-teal-300">
                    <flux:icon.sparkles class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                    <h3 class="font-bold text-sm">Assistente OCR / Leitura de Documentos</h3>
                </div>
                <p class="mt-1.5 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Carregue um documento (PDF, DOCX ou Imagem de planta/relatório) para preencher automaticamente os campos do projecto.
                </p>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Analisar Documento</label>
                    <input type="file" wire:model="documentoAnalise" accept=".pdf,.docx,.jpg,.jpeg,.png"
                           class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-teal-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-700 cursor-pointer" />
                </div>

                {{-- Estado de Leitura OCR --}}
                <div wire:loading wire:target="documentoAnalise" class="mt-3 rounded-xl bg-teal-100/70 p-3 text-xs text-teal-900 dark:bg-teal-950/60 dark:text-teal-200">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin text-teal-700 dark:text-teal-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>A realizar leitura OCR e extração de metadados...</span>
                    </div>
                </div>

                {{-- Resultados Extraídos pelo OCR --}}
                @if($dadosExtraidos && !isset($dadosExtraidos['erro']))
                    @foreach($dadosExtraidos['avisos'] ?? [] as $aviso)
                        <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">{{ $aviso }}</p>
                    @endforeach

                    <div class="mt-4 space-y-3 rounded-xl border border-teal-200 bg-white p-4 text-xs dark:border-teal-900/50 dark:bg-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-teal-700 dark:text-teal-300 uppercase tracking-wider text-[10px]">Dados Detectados</span>
                            <button type="button" wire:click="aplicarTodasSugestoes" class="text-[11px] font-semibold text-teal-600 hover:underline dark:text-teal-400">
                                Aplicar Todos
                            </button>
                        </div>

                        @if(!empty($dadosExtraidos['titulo_sugerido']))
                            <div>
                                <span class="text-zinc-400 block text-[10px]">Título Sugerido:</span>
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $dadosExtraidos['titulo_sugerido'] }}</div>
                                <button type="button" wire:click="aplicarSugestao('titulo', '{{ addslashes($dadosExtraidos['titulo_sugerido']) }}')" class="mt-0.5 text-[10px] text-teal-600 hover:underline">
                                    Usar este título
                                </button>
                            </div>
                        @endif

                        @if(!empty($dadosExtraidos['ano_detectado']))
                            <div>
                                <span class="text-zinc-400 block text-[10px]">Ano Detectado:</span>
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $dadosExtraidos['ano_detectado'] }}</div>
                            </div>
                        @endif

                        @if(!empty($dadosExtraidos['resumo_extraido']))
                            <div>
                                <span class="text-zinc-400 block text-[10px]">Resumo do Documento:</span>
                                <p class="text-zinc-600 dark:text-zinc-300 line-clamp-3">{{ $dadosExtraidos['resumo_extraido'] }}</p>
                                <button type="button" wire:click="aplicarSugestao('descricao', '{{ addslashes($dadosExtraidos['resumo_extraido']) }}')" class="mt-0.5 text-[10px] text-teal-600 hover:underline">
                                    Usar esta descrição
                                </button>
                            </div>
                        @endif
                    </div>
                @elseif($dadosExtraidos && isset($dadosExtraidos['erro']))
                    <div class="mt-3 rounded-lg bg-amber-50 p-2.5 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                        {{ $dadosExtraidos['erro'] }}
                    </div>
                @endif
            </div>

            {{-- Dicas de Preenchimento --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Instruções Técnicas</h4>
                <ul class="text-xs text-zinc-600 dark:text-zinc-400 space-y-2">
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-teal-600 shrink-0 mt-0.5" />
                        <span>Selecione <strong>Avanços de Obras</strong> para registar relatórios periódicos e galeria fotográfica de acompanhamento.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-teal-600 shrink-0 mt-0.5" />
                        <span>Indique a <strong>Fase</strong> para organizar cronologicamente o progresso do projecto.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-teal-600 shrink-0 mt-0.5" />
                        <span>Ficheiros enviados recebem carimbo de autenticidade e auditoria de acesso.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
