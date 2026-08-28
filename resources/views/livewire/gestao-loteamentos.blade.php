<div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl">Projectos de Loteamento</flux:heading>
                <flux:text class="mt-1">Loteamentos aprovados, concluídos e em curso</flux:text>
            </div>
            @if($podeEditar)
                <flux:button wire:click="abrirFormulario" variant="primary" icon="plus">
                    Novo Loteamento
                </flux:button>
            @endif
        </div>

        {{-- Filtros --}}
        <div class="mb-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filtroEstado" class="w-48">
                <flux:select.option value="">Todos os estados</flux:select.option>
                <flux:select.option value="aprovado">Aprovados</flux:select.option>
                <flux:select.option value="concluido">Concluídos</flux:select.option>
                <flux:select.option value="em_curso">Em Curso</flux:select.option>
            </flux:select>
            @if($municipios->isNotEmpty())
                <flux:select wire:model.live="municipioId" class="w-48">
                    <flux:select.option value="">Todos os municípios</flux:select.option>
                    @foreach($municipios as $mun)
                        <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        {{-- Tabela --}}
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Município</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Localização</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ficheiros</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">QR</th>
                        @if($podeEditar)
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acções</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($loteamentos as $lot)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-4 py-3 text-sm font-medium">
                                <a href="{{ route('loteamento.detalhe', $lot->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $lot->nome }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $lot->municipio?->nome }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $lot->estado === 'aprovado' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                    {{ $lot->estado === 'em_curso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                    {{ $lot->estado === 'concluido' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                                ">
                                    {{ str_replace('_', ' ', ucfirst($lot->estado)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $lot->localizacao }}</td>
                            <td class="px-4 py-3 text-sm">{{ $lot->ficheiros->count() }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $lot->created_at->format('d/m/Y') }}</td>
                            {{-- QR Code inline pequeno --}}
                            <td class="px-4 py-3">
                                <div class="rounded border border-zinc-200 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800 inline-block"
                                     title="QR — {{ $lot->nome }}">
                                    <canvas
                                        x-data
                                        x-init="renderQrCode($el, '{{ route('loteamento.detalhe', $lot->id) }}', 48)"
                                        width="48"
                                        height="48"
                                        class="block"
                                    ></canvas>
                                </div>
                            </td>
                            @if($podeEditar)
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="editar({{ $lot->id }})" size="sm" variant="ghost" icon="pencil" />
                                    @if($podeEliminar)
                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmação', message: 'Enviar este loteamento para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $lot->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500">Nenhum loteamento encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $loteamentos->links() }}</div>

        {{-- Modal Form --}}
        @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Editar' : 'Carregar' }} Loteamento</flux:heading>
                <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">
                    Indique o nome do loteamento e faça o upload dos ficheiros. As restantes informações são preenchidas automaticamente.
                </p>

                <div class="space-y-4">
                    {{-- Nome (campo principal) --}}
                    <flux:input wire:model="nome" label="Nome do Loteamento" placeholder="Ex: Loteamento Bairro Nova Vida" required />

                    {{-- Município (apenas para utilizadores com acesso a vários municípios) --}}
                    @if(!auth()->user()->isMunicipal() && $municipios->isNotEmpty())
                    <flux:select wire:model="municipioId" label="Município" required>
                        <flux:select.option value="">Seleccione o município...</flux:select.option>
                        @foreach($municipios as $mun)
                            <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @endif

                    {{-- Upload (campo principal) --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Upload de Ficheiros
                            <span class="ml-1 text-xs font-normal text-zinc-400">(máx. 100MB por ficheiro)</span>
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
                    </div>

                    {{-- Opções avançadas --}}
                    <details class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <summary class="cursor-pointer px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg select-none">
                            Opções avançadas (estado, localização, descrição)
                        </summary>
                        <div class="space-y-4 p-4">
                            <flux:select wire:model="estado" label="Estado">
                                <flux:select.option value="em_curso">Em Curso</flux:select.option>
                                <flux:select.option value="aprovado">Aprovado</flux:select.option>
                                <flux:select.option value="concluido">Concluído</flux:select.option>
                            </flux:select>
                            <flux:input wire:model="localizacao" label="Localização" />
                            <flux:textarea wire:model="descricao" label="Descrição" rows="3" />
                        </div>
                    </details>
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
                        <span wire:loading.remove wire:target="guardar">{{ $editingId ? 'Actualizar' : 'Guardar e Gerar QR' }}</span>
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
