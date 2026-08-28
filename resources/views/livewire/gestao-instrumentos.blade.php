<div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl">Instrumentos</flux:heading>
                <flux:text class="mt-1">Instrumentos de ordenamento do território, urbanísticos e cartas municipais</flux:text>
            </div>
            @if($podeEditar)
                <flux:button wire:click="abrirFormulario" variant="primary" icon="plus">
                    Novo Instrumento
                </flux:button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            {{-- Sidebar: Categorias e Tipos --}}
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">Categorias</flux:heading>

                    <button wire:click="$set('categoriaId', null)" class="mb-1 block w-full rounded px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ !$categoriaId ? 'bg-zinc-100 dark:bg-zinc-800 font-semibold' : '' }}">
                        Todas
                    </button>

                    @foreach($categorias as $cat)
                        <div class="mb-1">
                            <button wire:click="selectCategoria({{ $cat->id }})" class="block w-full rounded px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $categoriaId === $cat->id ? 'bg-zinc-100 dark:bg-zinc-800 font-semibold' : '' }}">
                                {{ $cat->nome }}
                            </button>

                            @if($categoriaId === $cat->id)
                                <div class="ml-4 mt-1 space-y-1">
                                    @foreach($cat->tipoInstrumentos as $tipo)
                                        <button wire:click="selectTipo({{ $tipo->id }})" class="block w-full rounded px-3 py-1.5 text-left text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $tipoInstrumentoId === $tipo->id ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 font-semibold' : '' }}">
                                            {{ $tipo->nome }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($municipios->isNotEmpty())
                <div class="mt-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">Filtrar Município</flux:heading>
                    <flux:select wire:model.live="municipioId" placeholder="Todos os municípios">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach($municipios as $mun)
                            <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                @endif
            </div>

            {{-- Main: Lista de Instrumentos --}}
            <div class="lg:col-span-3">
                <div class="mb-4">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar instrumentos..." icon="magnifying-glass" />
                </div>

                <div class="space-y-3">
                    @forelse($instrumentos as $inst)
                        {{-- Card do instrumento com QR no canto superior direito --}}
                        <div class="relative rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">

                            {{-- QR Code — canto superior direito --}}
                            <div class="absolute right-4 top-4 z-10">
                                <div class="rounded-lg border border-zinc-200 bg-white p-1.5 shadow-sm dark:border-zinc-600 dark:bg-zinc-800"
                                     title="Aponte a câmara para aceder ao detalhe deste documento">
                                    <canvas
                                        x-data
                                        x-init="renderQrCode($el, '{{ route('instrumento.detalhe', $inst->id) }}', 72)"
                                        width="72"
                                        height="72"
                                        class="block"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="flex items-start justify-between">
                                <div class="flex-1 pr-24">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('instrumento.detalhe', $inst->id) }}"
                                           class="font-semibold text-zinc-900 hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400 text-sm">
                                            {{ $inst->titulo }}
                                        </a>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $inst->estado === 'aprovado'  ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                            {{ $inst->estado === 'submetido' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                            {{ $inst->estado === 'rascunho'  ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                                            {{ $inst->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                        ">
                                            {{ ucfirst($inst->estado) }}
                                        </span>
                                    </div>
                                    <flux:text class="mt-1 text-xs">
                                        {{ $inst->tipoInstrumento?->categoria?->nome }} &rarr; {{ $inst->tipoInstrumento?->nome }}
                                    </flux:text>
                                    <flux:text class="text-xs text-zinc-500">
                                        {{ $inst->municipio?->nome }} &middot; {{ $inst->ano }} &middot; por {{ $inst->criadoPor?->name }}
                                    </flux:text>
                                    @if($inst->descricao)
                                        <flux:text class="mt-2 text-sm">{{ Str::limit($inst->descricao, 150) }}</flux:text>
                                    @endif

                                    {{-- Ficheiros --}}
                                    @if($inst->ficheiros->count())
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($inst->ficheiros as $f)
                                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ $f->nome_original }} ({{ strtoupper($f->tipo_ficheiro) }})
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if($podeEditar)
                                    <div class="flex flex-col gap-1 ml-2">
                                        <flux:button wire:click="editar({{ $inst->id }})" size="sm" variant="ghost" icon="pencil" />
                                        @if($podeEliminar)
                                            <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmação', message: 'Enviar este instrumento para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $inst->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Nenhum instrumento encontrado.</flux:text>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $instrumentos->links() }}
                </div>
            </div>
        </div>

        {{-- Modal/Form --}}
        @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <flux:heading size="lg" class="mb-1">
                    {{ $editingId ? 'Editar Instrumento' : 'Carregar Documento' }}
                </flux:heading>
                <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">
                    Preencha o nome do documento e faça o upload do ficheiro. As restantes informações são preenchidas automaticamente.
                </p>

                <div class="space-y-4">

                    {{-- Tipo (obrigatório) --}}
                    <flux:select wire:model="tipoInstrumentoId" label="Tipo de Instrumento" required>
                        <flux:select.option value="">Seleccione o tipo...</flux:select.option>
                        @foreach($categorias as $cat)
                            <optgroup label="{{ $cat->nome }}">
                                @foreach($cat->tipoInstrumentos as $tipo)
                                    <flux:select.option value="{{ $tipo->id }}">{{ $tipo->nome }}</flux:select.option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </flux:select>

                    {{-- Município (apenas para utilizadores com acesso a vários municípios) --}}
                    @if(!auth()->user()->isMunicipal() && $municipios->isNotEmpty())
                    <flux:select wire:model="municipioId" label="Município" required>
                        <flux:select.option value="">Seleccione o município...</flux:select.option>
                        @foreach($municipios as $mun)
                            <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @endif

                    {{-- Nome do documento (campo principal) --}}
                    <flux:input wire:model="titulo" label="Nome do Documento" placeholder="Ex: Plano Director Municipal 2024" required />

                    {{-- Upload de ficheiros (campo principal) --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Upload de Ficheiros
                            <span class="ml-1 text-xs font-normal text-zinc-400">(PDF, QGIS, DWG, ArcGIS, KMZ — máx. 100MB)</span>
                        </label>
                        <input type="file" wire:model="ficheiros" multiple
                            class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" />

                        <!-- Loading indicator with progress -->
                        <div wire:loading wire:target="ficheiros" class="mt-3 space-y-2">
                            <div class="flex items-center gap-2 text-xs text-indigo-600 dark:text-indigo-400">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                A carregar ficheiros...
                            </div>
                            <!-- Progress bar -->
                            <div class="w-full bg-zinc-200 rounded-full h-2 dark:bg-zinc-700">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                     style="width: {{ $this->uploadProgress }}%">
                                </div>
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 text-right">
                                {{ $this->uploadProgress }}%
                            </div>
                        </div>

                        <!-- Upload complete indicator -->
                        <div wire:loading.remove wire:target="ficheiros">
                            @if(count($ficheiros) > 0)
                                <div class="mt-3 flex items-center gap-2 p-3 rounded bg-red-50 dark:bg-red-900/20">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm font-medium text-red-700 dark:text-red-300">
                                        {{ count($ficheiros) }} ficheiro(s) carregado(s) com sucesso
                                    </span>
                                </div>
                            @endif
                        </div>

                        @error('ficheiros.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Campos avançados (colapsáveis) — apenas para admins / edição --}}
                    @if(auth()->user()->isInotu() || $editingId)
                    <details class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <summary class="cursor-pointer px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg select-none">
                            Opções avançadas (estado, ano, descrição)
                        </summary>
                        <div class="space-y-4 p-4">
                            <flux:input wire:model="ano" label="Ano" placeholder="{{ date('Y') }}" />
                            <flux:select wire:model="estado" label="Estado">
                                <flux:select.option value="rascunho">Rascunho</flux:select.option>
                                <flux:select.option value="submetido">Submetido</flux:select.option>
                                @if(auth()->user()->isInotu())
                                <flux:select.option value="aprovado">Aprovado</flux:select.option>
                                <flux:select.option value="rejeitado">Rejeitado</flux:select.option>
                                @endif
                            </flux:select>
                            <flux:textarea wire:model="descricao" label="Descrição" rows="3" placeholder="Informações adicionais sobre o documento..." />
                        </div>
                    </details>
                    @endif

                </div>

                @if($errors->any())
                    <div class="mt-4 rounded bg-red-50 p-3 dark:bg-red-900/20">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showForm', false)" variant="ghost" wire:loading.attr="disabled" wire:target="ficheiros">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="guardar"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="guardar,ficheiros"
                        :disabled="$isUploading || (count($ficheiros) === 0 && !$editingId)"
                    >
                        <span wire:loading.remove wire:target="guardar">
                            {{ $editingId ? 'Actualizar' : 'Guardar e Gerar QR' }}
                        </span>
                        <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A guardar...
                        </span>
                    </flux:button>
                </div>
            </div>
        </div>
        @endif
</div>
