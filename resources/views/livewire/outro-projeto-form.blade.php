<div class="space-y-6">
    {{-- Cabeçalho e Navegação --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Outros Projectos</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $registoId ? 'Editar Projecto' : 'Novo Projecto' }}</span>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <flux:heading size="xl" class="font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $registoId ? 'Editar Projecto' : 'Novo Projecto' }}
                </flux:heading>
                <flux:text class="mt-0.5 text-zinc-600 dark:text-zinc-400">
                    Dados técnicos, planeamento financeiro e execução — {{ $municipio->nome }}
                </flux:text>
            </div>
            <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" wire:navigate>
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
                    Identificação do Projecto
                </flux:heading>

                {{-- Título e Tipo --}}
                <div class="grid gap-4 sm:grid-cols-[2fr_1fr]">
                    <flux:input wire:model="titulo" label="Título do Projecto" placeholder="Ex: Construção da Praça Central e Requalificação Viária" required />
                    <flux:select wire:model.live="tipo" label="Tipo de Projecto">
                        <flux:select.option value="requalificacao">Requalificação</flux:select.option>
                        <flux:select.option value="infraestrutura">Infraestrutura</flux:select.option>
                        <flux:select.option value="social">Social</flux:select.option>
                        <flux:select.option value="outro">Outro</flux:select.option>
                    </flux:select>
                </div>

                {{-- Entidade Responsável e Fase --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="entidade_responsavel" label="Entidade Responsável" placeholder="Ex: Administração Municipal / Empreiteiro" />
                    <div>
                        <flux:input wire:model="fase" label="Fase Actual" placeholder="Ex: Fase 1" />
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="text-[11px] text-zinc-400 self-center mr-1">Atalhos:</span>
                            @foreach(['Fase 1', 'Fase 2', 'Fase 3', 'Conclusão'] as $faseOpcao)
                                <button type="button" wire:click="definirFase('{{ $faseOpcao }}')"
                                        class="rounded-md border px-2 py-0.5 text-xs font-medium transition {{ $fase === $faseOpcao ? 'border-cyan-500 bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ $faseOpcao }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Estado de Execução --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="estado" label="Estado do Projecto">
                        <flux:select.option value="planificado">Planificado</flux:select.option>
                        <flux:select.option value="em_curso">Em Curso</flux:select.option>
                        <flux:select.option value="concluido">Concluído</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>
                    <div>
                        <flux:input wire:model="orcamento" label="Orçamento Estimado (AOA)" placeholder="Ex: 25000000.00" type="number" step="0.01" />
                        <span class="text-[11px] text-zinc-400 mt-1 block">Valor monetário previsto para o projecto em Kwanzas.</span>
                    </div>
                </div>

                {{-- Cronograma e Prazos --}}
                <div class="rounded-xl border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40 space-y-3">
                    <div class="text-xs font-semibold uppercase text-zinc-500 tracking-wider">Cronograma e Datas</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="data_inicio" label="Data de Início" type="date" />
                        <flux:input wire:model="data_previsao_fim" label="Previsão de Conclusão" type="date" />
                    </div>

                    {{-- Atalhos de Data --}}
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[11px] text-zinc-400 mr-1">Atalhos rápidos:</span>
                        <button type="button" wire:click="definirAtalhoData('inicio_hoje')" class="rounded-md border border-zinc-200 bg-white px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            Início Hoje
                        </button>
                        <button type="button" wire:click="definirAtalhoData('fim_6_meses')" class="rounded-md border border-zinc-200 bg-white px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            +6 Meses
                        </button>
                        <button type="button" wire:click="definirAtalhoData('fim_1_ano')" class="rounded-md border border-zinc-200 bg-white px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            +1 Ano
                        </button>
                        <button type="button" wire:click="definirAtalhoData('fim_2_anos')" class="rounded-md border border-zinc-200 bg-white px-2 py-0.5 text-xs text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            +2 Anos
                        </button>
                    </div>
                </div>

                {{-- Descrição e Observações --}}
                <div>
                    <flux:textarea wire:model="descricao" label="Descrição do Projecto" rows="4"
                                   placeholder="Resumo técnico, âmbito territorial e principais intervenções previstas." />
                </div>

                <div>
                    <flux:textarea wire:model="observacoes" label="Observações Complementares" rows="2"
                                   placeholder="Notas adicionais, condicionantes ou referências a deliberações." />
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
                <a href="{{ route('municipio.requalificacao.outros', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,documentoAnalise">
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
            <div class="rounded-2xl border border-cyan-200 bg-gradient-to-b from-cyan-500/10 via-cyan-500/5 to-transparent p-6 shadow-sm dark:border-cyan-900/50 dark:bg-zinc-900">
                <div class="flex items-center gap-2 text-cyan-800 dark:text-cyan-300">
                    <flux:icon.sparkles class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                    <h3 class="font-bold text-sm">Assistente OCR / Leitura de Dossier</h3>
                </div>
                <p class="mt-1.5 text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Carregue a memória descritiva, proposta técnica ou documento do projecto (PDF, DOCX ou Imagem) para preenchimento assistido.
                </p>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Analisar Documento</label>
                    <input type="file" wire:model="documentoAnalise" accept=".pdf,.docx,.jpg,.jpeg,.png"
                           class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-cyan-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-cyan-700 cursor-pointer" />
                </div>

                {{-- Estado de Leitura OCR --}}
                <div wire:loading wire:target="documentoAnalise" class="mt-3 rounded-xl bg-cyan-100/70 p-3 text-xs text-cyan-900 dark:bg-cyan-950/60 dark:text-cyan-200">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin text-cyan-700 dark:text-cyan-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>A realizar leitura OCR e extração inteligente...</span>
                    </div>
                </div>

                {{-- Resultados Extraídos pelo OCR --}}
                @if($dadosExtraidos && !isset($dadosExtraidos['erro']))
                    @foreach($dadosExtraidos['avisos'] ?? [] as $aviso)
                        <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">{{ $aviso }}</p>
                    @endforeach

                    <div class="mt-4 space-y-3 rounded-xl border border-cyan-200 bg-white p-4 text-xs dark:border-cyan-900/50 dark:bg-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-cyan-700 dark:text-cyan-300 uppercase tracking-wider text-[10px]">Dados Detectados</span>
                            <button type="button" wire:click="aplicarTodasSugestoes" class="text-[11px] font-semibold text-cyan-600 hover:underline dark:text-cyan-400">
                                Aplicar Todos
                            </button>
                        </div>

                        @if(!empty($dadosExtraidos['titulo_sugerido']))
                            <div>
                                <span class="text-zinc-400 block text-[10px]">Título Sugerido:</span>
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $dadosExtraidos['titulo_sugerido'] }}</div>
                                <button type="button" wire:click="aplicarSugestao('titulo', '{{ addslashes($dadosExtraidos['titulo_sugerido']) }}')" class="mt-0.5 text-[10px] text-cyan-600 hover:underline">
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
                                <button type="button" wire:click="aplicarSugestao('descricao', '{{ addslashes($dadosExtraidos['resumo_extraido']) }}')" class="mt-0.5 text-[10px] text-cyan-600 hover:underline">
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
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Classificação de Projectos</h4>
                <ul class="text-xs text-zinc-600 dark:text-zinc-400 space-y-2">
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-cyan-600 shrink-0 mt-0.5" />
                        <span><strong>Requalificação:</strong> Intervenções em malhas urbanas, espaços públicos e requalificação paisagística.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-cyan-600 shrink-0 mt-0.5" />
                        <span><strong>Infraestrutura:</strong> Vias, saneamento, redes de iluminação e drenagem.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <flux:icon.check class="h-4 w-4 text-cyan-600 shrink-0 mt-0.5" />
                        <span><strong>Social:</strong> Equipamentos públicos, escolas, centros comunitários e de saúde.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
