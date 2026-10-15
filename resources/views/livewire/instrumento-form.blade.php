<div class="space-y-6">
    {{-- Navegação e Cabeçalho --}}
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ $categoriaRoute }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $categoriaNome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $tipo->nome }}</a>
            <span>/</span>
            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $registoId ? 'Editar' : 'Novo Registo' }}</span>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon name="map" class="h-6 w-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="xl">{{ $registoId ? 'Editar Registo' : 'Novo Registo' }}</flux:heading>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                            {{ $tipo->nome }}
                        </span>
                    </div>
                    <flux:text class="mt-0.5">
                        {{ $municipio->nome }} &bull; Província da Huíla &bull; Leitura OCR e preenchimento inteligente
                    </flux:text>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar à lista</flux:button>
                </a>
            </div>
        </div>
    </div>

    {{-- Banner de Progresso do OCR (quando em execução) --}}
    @if($ocrProcessando)
        <div class="animate-pulse rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 via-sky-50 to-indigo-50 p-4 dark:border-indigo-800/50 dark:from-indigo-950/40 dark:via-sky-950/40 dark:to-indigo-950/40">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">A processar leitura OCR do documento...</p>
                    <p class="text-xs text-indigo-700 dark:text-indigo-300">A analisar páginas, ano de vigência, título institucional e sumário executivo.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Painel de Resultados do OCR (quando concluído com sucesso) --}}
    @if($ocrSucesso && $ocrDados)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-950/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                        <flux:icon name="sparkles" class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Leitura OCR e Análise de Documento Concluída</h3>
                            <span class="rounded-full bg-emerald-200/80 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-800/60 dark:text-emerald-200">
                                {{ $ocrDados['paginas_processadas'] ?? $ocrDados['num_paginas'] ?? 1 }} pág. analisadas
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-emerald-700 dark:text-emerald-300">
                            {{ $ocrMensagem }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <flux:button size="sm" variant="primary" wire:click="aplicarSugestoesOcr" icon="check">
                        Aplicar Todas as Sugestões
                    </flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="toggleMostrarTextoOcr" icon="document-magnifying-glass">
                        {{ $mostrarTextoOcr ? 'Ocultar Texto' : 'Ver Texto Extraído' }}
                    </flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="limparOcr" icon="x-mark" />
                </div>
            </div>

            {{-- Pílulas com dados detectados pelo OCR --}}
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-emerald-200/80 bg-white/80 p-3 shadow-xs dark:border-emerald-800/40 dark:bg-zinc-900/80">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ano Detectado</span>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-base font-bold text-emerald-700 dark:text-emerald-300">{{ $ocrDados['ano'] ?? '-' }}</span>
                        @if(($ocrDados['ano'] ?? null) !== $ano)
                            <button type="button" wire:click="aplicarCampoOcr('ano')" class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/60 dark:text-emerald-200">
                                Aplicar
                            </button>
                        @else
                            <span class="text-xs text-emerald-600 dark:text-emerald-400">&check; Aplicado</span>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-emerald-200/80 bg-white/80 p-3 shadow-xs dark:border-emerald-800/40 dark:bg-zinc-900/80">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado Sugerido</span>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-xs font-semibold capitalize text-emerald-800 dark:text-emerald-200">{{ $ocrDados['estado'] ?? 'Rascunho' }}</span>
                        @if(($ocrDados['estado'] ?? null) !== $estado)
                            <button type="button" wire:click="aplicarCampoOcr('estado')" class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/60 dark:text-emerald-200">
                                Aplicar
                            </button>
                        @else
                            <span class="text-xs text-emerald-600 dark:text-emerald-400">&check; Aplicado</span>
                        @endif
                    </div>
                </div>

                <div class="sm:col-span-2 rounded-xl border border-emerald-200/80 bg-white/80 p-3 shadow-xs dark:border-emerald-800/40 dark:bg-zinc-900/80">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Título Identificado</span>
                    <div class="mt-1 flex items-center justify-between gap-2">
                        <span class="truncate text-xs font-medium text-zinc-800 dark:text-zinc-200" title="{{ $ocrDados['titulo'] ?? '' }}">
                            {{ $ocrDados['titulo'] ?? '-' }}
                        </span>
                        @if(($ocrDados['titulo'] ?? null) !== $titulo)
                            <button type="button" wire:click="aplicarCampoOcr('titulo')" class="shrink-0 rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/60 dark:text-emerald-200">
                                Aplicar
                            </button>
                        @else
                            <span class="shrink-0 text-xs text-emerald-600 dark:text-emerald-400">&check; Aplicado</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pré-visualização do texto extraído pelo OCR --}}
            @if($mostrarTextoOcr && !empty($ocrTextoPreview))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Trecho do Documento Reconhecido pelo OCR</span>
                        <span class="text-xs text-zinc-400">{{ strlen($ocrTextoPreview) }} caracteres extraídos</span>
                    </div>
                    <pre class="max-h-56 overflow-y-auto whitespace-pre-wrap rounded-lg bg-zinc-50 p-3 text-xs leading-relaxed text-zinc-700 dark:bg-zinc-950 dark:text-zinc-300 font-mono">{{ $ocrTextoPreview }}</pre>
                </div>
            @endif
        </div>
    @endif

    {{-- Layout do Formulário Principal --}}
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Coluna Principal: Formulário --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-6">
                    {{-- Título do Instrumento --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                Título do Instrumento <span class="text-red-500">*</span>
                            </label>
                            @if($ocrDados && !empty($ocrDados['titulo']) && $titulo !== $ocrDados['titulo'])
                                <button type="button" wire:click="aplicarCampoOcr('titulo')" class="text-xs text-indigo-600 hover:underline dark:text-indigo-400 flex items-center gap-1">
                                    <flux:icon name="sparkles" class="h-3 w-3" /> Usar título detectado
                                </button>
                            @endif
                        </div>
                        <flux:input wire:model="titulo" placeholder="Ex: {{ $tipo->nome }} do Município de {{ $municipio->nome }} ({{ $ano ?: date('Y') }})" required />
                        <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">
                            Designação oficial do plano ou instrumento de ordenamento territorial.
                        </span>
                    </div>

                    {{-- Bloco do Ano (com Preenchimento Automático) e Estado --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        {{-- Campo de Ano com Preenchimento Automático --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-1.5">
                                    <flux:icon name="calendar" class="h-4 w-4 text-zinc-500" />
                                    Ano de Vigência / Elaboração
                                </label>
                                <button type="button" wire:click="preencherAnoAtual"
                                    class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-xs font-semibold text-indigo-700 shadow-xs ring-1 ring-zinc-200 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-indigo-300 dark:ring-zinc-700"
                                    title="Preencher automaticamente com o ano corrente">
                                    <flux:icon name="bolt" class="h-3 w-3" /> Ano Atual ({{ date('Y') }})
                                </button>
                            </div>

                            <flux:input wire:model="ano" placeholder="{{ date('Y') }}" maxlength="4" class="font-semibold text-zinc-900 dark:text-zinc-100" />

                            {{-- Atalhos Rápidos para Anos --}}
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Atalhos:</span>
                                @foreach([date('Y'), date('Y') - 1, date('Y') - 2, date('Y') - 3, date('Y') - 4] as $anoOpcao)
                                    <button type="button" wire:click="definirAno('{{ $anoOpcao }}')"
                                        class="rounded-md px-2 py-0.5 text-xs font-medium transition
                                            {{ $ano === (string) $anoOpcao
                                                ? 'bg-red-600 text-white shadow-xs'
                                                : 'bg-white text-zinc-700 ring-1 ring-zinc-200 hover:bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700' }}">
                                        {{ $anoOpcao }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Campo de Estado --}}
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 block mb-1.5">
                                Estado do Instrumento <span class="text-red-500">*</span>
                            </label>
                            <flux:select wire:model="estado">
                                <flux:select.option value="rascunho">Rascunho (Em elaboração preliminar)</flux:select.option>
                                <flux:select.option value="submetido">Submetido (Em análise / Consulta pública)</flux:select.option>
                                <flux:select.option value="aprovado">Aprovado (Homologado / Em vigor)</flux:select.option>
                                <flux:select.option value="rejeitado">Rejeitado (Necessita de revisão técnica)</flux:select.option>
                            </flux:select>

                            <div class="mt-2.5 flex items-center gap-2">
                                <span class="text-xs text-zinc-500">Status atual:</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $estado === 'aprovado'  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                                    {{ $estado === 'submetido' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                    {{ $estado === 'rascunho'  ? 'bg-zinc-200 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                                    {{ $estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                ">
                                    {{ ucfirst($estado) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Descrição / Memória Descritiva / Enquadramento --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                Descrição, Memória Técnica e Enquadramento
                            </label>
                            @if($ocrDados && !empty($ocrDados['descricao']) && $descricao !== $ocrDados['descricao'])
                                <button type="button" wire:click="aplicarCampoOcr('descricao')" class="text-xs text-indigo-600 hover:underline dark:text-indigo-400 flex items-center gap-1">
                                    <flux:icon name="sparkles" class="h-3 w-3" /> Usar resumo extraído pelo OCR
                                </button>
                            @endif
                        </div>
                        <flux:textarea wire:model="descricao" rows="4"
                            placeholder="Resumo dos objectivos, modelo territorial, cobertura espacial (comunas/bairros), legislação aplicável ou notas técnicas do plano." />
                        <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">
                            Pode ser preenchido manualmente ou automaticamente via leitura OCR do documento.
                        </span>
                    </div>

                    {{-- Secção de Upload de Ficheiros com Suporte a OCR --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                    <flux:icon name="paper-clip" class="h-4 w-4 text-zinc-500" />
                                    Documentos e Peças Técnicas do Plano
                                </h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Formatos aceites: PDF, DOCX, DWG, DXF, SHP, QGIS, ZIP (máx. 50MB por ficheiro)
                                </p>
                            </div>

                            @if((count($ficheiros) > 0 || count($ficheirosExistentes) > 0) && !$ocrProcessando)
                                <flux:button size="sm" variant="subtle" wire:click="executarLeituraManual" icon="sparkles" class="shrink-0">
                                    Ler Documento (OCR)
                                </flux:button>
                            @endif
                        </div>

                        {{-- Dropzone de Upload --}}
                        <div class="relative">
                            <input type="file" wire:model="ficheiros" multiple id="ficheiros_input"
                                accept=".pdf,.doc,.docx,.dwg,.dxf,.shp,.qgs,.qgz,.zip,.png,.jpg,.jpeg"
                                class="block w-full cursor-pointer rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/50 p-6 text-center text-sm text-zinc-500 transition hover:border-red-400 hover:bg-red-50/20 dark:border-zinc-700 dark:bg-zinc-950/50 dark:hover:border-red-500" />
                        </div>

                        {{-- Progresso de Carregamento Livewire --}}
                        <div wire:loading wire:target="ficheiros" class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-400">
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin text-red-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    A carregar ficheiros para o servidor...
                                </span>
                                <span class="font-semibold">{{ $uploadProgress }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div class="h-2 rounded-full bg-red-600 transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                            </div>
                        </div>

                        {{-- Lista de Novos Ficheiros Anexados --}}
                        @if(count($ficheiros) > 0)
                            <div class="mt-4 space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Novos Ficheiros Selecionados ({{ count($ficheiros) }})
                                </span>
                                <div class="divide-y divide-zinc-100 rounded-xl border border-zinc-200 bg-zinc-50/60 dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-950/30">
                                    @foreach($ficheiros as $index => $f)
                                        <div class="flex items-center justify-between p-3 text-xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300">
                                                    <flux:icon name="document-text" class="h-4 w-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $f->getClientOriginalName() }}
                                                    </p>
                                                    <p class="text-[11px] text-zinc-400">
                                                        {{ strtoupper($f->getClientOriginalExtension()) }} &bull; {{ number_format($f->getSize() / 1024, 1) }} KB
                                                    </p>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removerFicheiroNovo({{ $index }})"
                                                class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-200 hover:text-red-600 dark:hover:bg-zinc-800 dark:hover:text-red-400"
                                                title="Remover anexo">
                                                <flux:icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Lista de Ficheiros Já Gravados no Servidor (em caso de edição) --}}
                        @if(count($ficheirosExistentes) > 0)
                            <div class="mt-4 space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    Ficheiros Anexados Atualmente ({{ count($ficheirosExistentes) }})
                                </span>
                                <div class="divide-y divide-zinc-100 rounded-xl border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                                    @foreach($ficheirosExistentes as $fExist)
                                        <div class="flex items-center justify-between p-3 text-xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                                    <flux:icon name="paper-clip" class="h-4 w-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <a href="{{ route('storage.serve', $fExist['caminho']) }}" target="_blank"
                                                        class="truncate font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                        {{ $fExist['nome_original'] }}
                                                    </a>
                                                    <p class="text-[11px] text-zinc-400">
                                                        {{ strtoupper($fExist['tipo_ficheiro']) }} &bull; {{ number_format($fExist['tamanho'] / 1024, 1) }} KB &bull; {{ $fExist['created_at'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('storage.serve', $fExist['caminho']) }}" target="_blank">
                                                    <flux:button size="xs" variant="ghost" icon="eye">Ver</flux:button>
                                                </a>
                                                <button type="button" wire:click="removerFicheiroExistente({{ $fExist['id'] }})"
                                                    onclick="return confirm('Deseja realmente eliminar este anexo?')"
                                                    class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-red-600 dark:hover:bg-zinc-800 dark:hover:text-red-400">
                                                    <flux:icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Exibição de Erros de Validação --}}
                @if($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30">
                        <div class="flex items-center gap-2 text-sm font-semibold text-red-800 dark:text-red-200 mb-1">
                            <flux:icon name="exclamation-circle" class="h-4 w-4 text-red-600" />
                            Por favor, corrija os seguintes erros:
                        </div>
                        <ul class="list-inside list-disc space-y-0.5 text-xs text-red-700 dark:text-red-300">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Barra de Submissão / Guardar --}}
                <div class="mt-6 flex items-center justify-between border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" wire:navigate>
                        <flux:button variant="ghost" wire:loading.attr="disabled">Cancelar</flux:button>
                    </a>

                    <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled"
                        :disabled="$isUploading || (count($ficheiros) === 0 && count($ficheirosExistentes) === 0 && !$registoId)">
                        <span wire:loading.remove wire:target="guardar">{{ $registoId ? 'Actualizar Instrumento' : 'Guardar Instrumento' }}</span>
                        <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A guardar registo...
                        </span>
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Coluna Lateral: Assistente Técnico & Resumo do PMOT --}}
        <aside class="space-y-6">
            {{-- Cartão Informativo sobre o PMOT / Instrumento --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                        <flux:icon name="information-circle" class="h-5 w-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $tipo->nome }}</h4>
                        <span class="text-xs text-zinc-500">{{ $municipio->nome }} &bull; Huíla</span>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                    <p>
                        O <strong>PMOT</strong> é o instrumento fundamental que estabelece as directrizes e o modelo de ocupação espacial do município, integrando zonamento, reservas fundiárias e infraestruturas urbanas.
                    </p>
                    <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-950/50 space-y-1.5">
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200 block text-[11px] uppercase tracking-wide">
                            Recursos de Produtividade
                        </span>
                        <div class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300">
                            <flux:icon name="bolt" class="h-3.5 w-3.5 text-amber-500" />
                            <span>Preenchimento automático do ano corrente.</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-300">
                            <flux:icon name="sparkles" class="h-3.5 w-3.5 text-indigo-500" />
                            <span>Leitura e extração OCR inteligente ao anexar PDF, DOCX ou imagem.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <dl class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-zinc-400">Município:</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $municipio->nome }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-400">Categoria:</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $categoriaNome }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-400">Ano selecionado:</dt>
                            <dd class="font-bold text-red-600 dark:text-red-400">{{ $ano ?: '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-400">Total de anexos:</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ count($ficheiros) + count($ficheirosExistentes) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Palavras-Chave / Tags Técnicas do Documento (se OCR correu) --}}
            @if($ocrDados && !empty($ocrDados['palavras_chave']))
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                        Tópicos Identificados no Documento
                    </h5>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($ocrDados['palavras_chave'] as $tag)
                            <span class="rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                # {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>
