<div>
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
                <span>/</span>
                <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
                <span>/</span>
                <a href="{{ route('municipio.requalificacao', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Requalificação</a>
                <span>/</span>
                <span>Projecto SONA</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Projecto SONA</flux:heading>
                    <flux:text class="mt-1">Planos de requalificação urbana e avanços de obras — {{ $municipio->nome }}</flux:text>
                </div>
                @if($podeEditar)
                    <a href="{{ route('municipio.requalificacao.sona.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus">Novo Projecto</flux:button>
                    </a>
                @endif
            </div>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filtroTipo" class="w-48">
                <flux:select.option value="">Todos os tipos</flux:select.option>
                <flux:select.option value="requalificacao">Requalificação Urbana</flux:select.option>
                <flux:select.option value="avancos_obras">Avanços de Obras</flux:select.option>
            </flux:select>
        </div>

        <div class="space-y-4">
            @forelse($projectos as $proj)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm">{{ $proj->titulo }}</flux:heading>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $proj->estado === 'em_curso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                    {{ $proj->estado === 'concluido' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                    {{ $proj->estado === 'suspenso' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                ">
                                    {{ str_replace('_', ' ', ucfirst($proj->estado)) }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $proj->tipo === 'requalificacao' ? 'Requalificação' : 'Avanços de Obras' }}
                                </span>
                            </div>
                            <flux:text class="mt-1 text-xs">
                                {{ $proj->municipio?->nome }}
                                @if($proj->fase) &middot; Fase: {{ $proj->fase }} @endif
                                &middot; {{ $proj->created_at->format('d/m/Y') }}
                            </flux:text>
                            @if($proj->descricao)
                                <flux:text class="mt-2 text-sm">{{ Str::limit($proj->descricao, 200) }}</flux:text>
                            @endif

                            {{-- Ficheiros/Imagens --}}
                            @if($proj->ficheiros->count())
                                <div class="mt-3">
                                    @if($proj->tipo === 'avancos_obras')
                                        {{-- Galeria de imagens --}}
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                            @foreach($proj->ficheiros as $f)
                                                @if(Str::startsWith($f->mime_type, 'image/'))
                                                    <div class="relative group">
                                                        <img src="{{ route('storage.serve', $f->caminho) }}" alt="{{ $f->legenda ?? $f->nome_original }}"
                                                             class="w-full h-24 object-cover rounded-lg border dark:border-zinc-700" />
                                                        @if($f->legenda)
                                                            <div class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-xs p-1 rounded-b-lg">
                                                                {{ $f->legenda }}
                                                            </div>
                                                        @endif
                                                        @if($f->fase)
                                                            <div class="absolute top-1 right-1 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded">
                                                                {{ $f->fase }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                       class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                                        {{ $f->nome_original }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($proj->ficheiros as $f)
                                                <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                                    {{ $f->nome_original }} ({{ strtoupper($f->tipo_ficheiro) }})
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($podeEditar)
                            <div class="flex gap-1">
                                <a href="{{ route('municipio.requalificacao.sona.editar', [$municipio, $proj->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                                @if($podeEliminar)
                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este projecto para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $proj->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Nenhum projecto SONA encontrado.</flux:text>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $projectos->links() }}</div>

        @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Editar' : 'Novo' }} Projecto SONA</flux:heading>
                <div class="space-y-4">
                    <flux:input wire:model="titulo" label="Título" required />
                    <flux:textarea wire:model="descricao" label="Descrição" rows="3" />

                    <flux:select wire:model="tipo" label="Tipo">
                        <flux:select.option value="requalificacao">Requalificação Urbana</flux:select.option>
                        <flux:select.option value="avancos_obras">Avanços de Obras</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="fase" label="Fase" placeholder="Ex: Fase 1, Fase 2" />

                    <flux:select wire:model="estado" label="Estado">
                        <flux:select.option value="em_curso">Em Curso</flux:select.option>
                        <flux:select.option value="concluido">Concluído</flux:select.option>
                        <flux:select.option value="suspenso">Suspenso</flux:select.option>
                    </flux:select>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ficheiros / Imagens</label>
                        <input type="file" wire:model="ficheiros" multiple class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />

                        <!-- Loading indicator with progress -->
                        <div wire:loading wire:target="ficheiros" class="mt-3 space-y-2">
                            <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                A carregar ficheiros...
                            </div>
                            <!-- Progress bar -->
                            <div class="w-full bg-zinc-200 rounded-full h-2 dark:bg-zinc-700">
                                <div class="bg-zinc-600 h-2 rounded-full transition-all duration-300"
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
                    </div>

                    <flux:input wire:model="faseFicheiro" label="Fase do Ficheiro" placeholder="Indicar a fase dos ficheiros" />
                    <flux:input wire:model="legenda" label="Legenda" placeholder="Legenda das imagens" />
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
                        {{ $editingId ? 'Actualizar' : 'Guardar' }}
                    </flux:button>
                </div>
            </div>
        </div>
        @endif
</div>
