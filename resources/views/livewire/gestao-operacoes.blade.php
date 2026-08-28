<div>
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
                <span>/</span>
                <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
                <span>/</span>
                <span>Operações Urbanísticas</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Operações Urbanísticas</flux:heading>
                    <flux:text class="mt-1">Croquis de localização, terrenos ociosos e pareceres — {{ $municipio->nome }}</flux:text>
                </div>
                @if($podeEditar)
                    <flux:button wire:click="abrirOperacaoForm" variant="primary" icon="plus">
                        Nova Operação
                    </flux:button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            {{-- Sidebar: Operações e Filtros --}}
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">Operações</flux:heading>
                    @forelse($operacoes as $op)
                        <button wire:click="selectOperacao({{ $op->id }})"
                            class="mb-1 block w-full rounded px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $operacaoId === $op->id ? 'bg-zinc-100 dark:bg-zinc-800 font-semibold' : '' }}">
                            <div>{{ $op->titulo }}</div>
                            <div class="text-xs text-zinc-500">{{ $op->municipio?->nome }}</div>
                        </button>
                    @empty
                        <flux:text class="text-sm text-zinc-500">Nenhuma operação criada.</flux:text>
                    @endforelse
                </div>
            </div>

            {{-- Main --}}
            <div class="lg:col-span-3">
                @if($operacaoId)
                    {{-- Tabs --}}
                    <div class="mb-4 flex gap-1 rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button wire:click="setTab('croquis_elaborados')"
                            class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition {{ $tab === 'croquis_elaborados' ? 'bg-white shadow dark:bg-zinc-700' : 'hover:bg-white/50 dark:hover:bg-zinc-700/50' }}">
                            Croquis Elaborados
                        </button>
                        <button wire:click="setTab('terrenos_ociosos')"
                            class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition {{ $tab === 'terrenos_ociosos' ? 'bg-white shadow dark:bg-zinc-700' : 'hover:bg-white/50 dark:hover:bg-zinc-700/50' }}">
                            Terrenos Ociosos
                        </button>
                    </div>

                    <div class="mb-4 flex gap-3">
                        <div class="flex-1">
                            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por coordenadas..." icon="magnifying-glass" />
                        </div>
                        @if($podeEditar)
                            <flux:button wire:click="abrirCroquiForm" variant="primary" icon="plus" size="sm">
                                Novo Croqui
                            </flux:button>
                        @endif
                    </div>

                    {{-- Tabela de Croquis --}}
                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">PDF</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Coordenadas</th>
                                    @if($tab === 'croquis_elaborados')
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Condição</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Proprietários</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                                    @if($podeEditar)
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acções</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                @forelse($croquis as $croqui)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        <td class="px-4 py-3 text-sm">
                                            @if($croqui->ficheiro_pdf_caminho)
                                                <a href="{{ route('storage.serve', $croqui->ficheiro_pdf_caminho) }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">
                                                    {{ $croqui->ficheiro_pdf_nome ?? 'Ver PDF' }}
                                                </a>
                                            @else
                                                <span class="text-zinc-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($croqui->latitude && $croqui->longitude)
                                                <span class="font-mono text-xs">{{ $croqui->latitude }}, {{ $croqui->longitude }}</span>
                                            @endif
                                            @if($croqui->coordenadas_texto)
                                                <div class="text-xs text-zinc-500">{{ $croqui->coordenadas_texto }}</div>
                                            @endif
                                        </td>
                                        @if($tab === 'croquis_elaborados')
                                        <td class="px-4 py-3">
                                            @if($croqui->condicao)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $croqui->condicao === 'legal' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                                {{ $croqui->condicao === 'ilegal' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                                {{ $croqui->condicao === 'em_conflito' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' : '' }}
                                            ">
                                                {{ str_replace('_', ' ', ucfirst($croqui->condicao)) }}
                                            </span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="px-4 py-3 text-sm">
                                            @foreach($croqui->proprietarios as $prop)
                                                <div>{{ $prop->nome }}</div>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $croqui->created_at->format('d/m/Y H:i') }}</td>
                                        @if($podeEditar)
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-1">
                                                <flux:button wire:click="editarCroqui({{ $croqui->id }})" size="sm" variant="ghost" icon="pencil" />
                                                @if($podeEliminar)
                                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmação', message: 'Enviar este croqui para a lixeira?', action: 'eliminarCroqui', component: $wire.$id, params: [{{ $croqui->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">Nenhum croqui encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($croquis instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">{{ $croquis->links() }}</div>
                    @endif
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                        <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <flux:heading size="sm" class="mt-3">Seleccione uma operação</flux:heading>
                        <flux:text class="mt-1 text-zinc-500">Escolha uma operação urbanística na barra lateral para ver os croquis.</flux:text>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal: Nova Operação --}}
        @if($showOperacaoForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Nova Operação Urbanística</flux:heading>
                <div class="space-y-4">
                    <flux:input wire:model="opTitulo" label="Título" required />
                    <flux:textarea wire:model="opDescricao" label="Descrição" rows="3" />

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ficheiro Base (QGIS/Croquis)</label>
                        <input type="file" wire:model="ficheiro_base" class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />

                        <!-- Loading indicator with progress -->
                        <div wire:loading wire:target="ficheiro_base" class="mt-2 space-y-1">
                            <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                A carregar ficheiro...
                            </div>
                            <div class="w-full bg-zinc-200 rounded-full h-1.5 dark:bg-zinc-700">
                                <div class="bg-zinc-600 h-1.5 rounded-full transition-all duration-300"
                                     style="width: {{ $this->uploadProgress }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showOperacaoForm', false)" variant="ghost" wire:loading.attr="disabled" wire:target="ficheiro_base">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="guardarOperacao"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="guardarOperacao,ficheiro_base"
                        :disabled="$isUploading"
                    >
                        Guardar
                    </flux:button>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal: Novo Croqui --}}
        @if($showCroquiForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900 max-h-[90vh] overflow-y-auto">
                <flux:heading size="lg" class="mb-4">{{ $editingCroquiId ? 'Editar' : 'Novo' }} Croqui</flux:heading>

                <div class="space-y-4">
                    @if($tipo === 'elaborado')
                    <flux:select wire:model="condicao" label="Condição">
                        <flux:select.option value="">Seleccione...</flux:select.option>
                        <flux:select.option value="legal">Legal</flux:select.option>
                        <flux:select.option value="ilegal">Ilegal</flux:select.option>
                        <flux:select.option value="em_conflito">Em Conflito</flux:select.option>
                    </flux:select>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="latitude" label="Latitude" type="number" step="0.0000001" />
                        <flux:input wire:model="longitude" label="Longitude" type="number" step="0.0000001" />
                    </div>

                    <flux:input wire:model="coordenadas_texto" label="Coordenadas (texto)" placeholder="Ex: S 14— 55' 23.4'', E 13— 29' 12.1''" />
                    <flux:textarea wire:model="observacoes" label="Observações" rows="2" />

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Croqui em PDF ou Word</label>
                        <input type="file" wire:model="ficheiro_pdf" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300" />

                        <!-- Loading indicator with progress -->
                        <div wire:loading wire:target="ficheiro_pdf" class="mt-2 space-y-1">
                            <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                A carregar ficheiro...
                            </div>
                            <div class="w-full bg-zinc-200 rounded-full h-1.5 dark:bg-zinc-700">
                                <div class="bg-zinc-600 h-1.5 rounded-full transition-all duration-300"
                                     style="width: {{ $this->uploadProgress }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Upload complete indicator -->
                        <div wire:loading.remove wire:target="ficheiro_pdf">
                            @if($ficheiro_pdf)
                                <div class="mt-2 flex items-center gap-2 p-2 rounded bg-red-50 dark:bg-red-900/20">
                                    <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-xs font-medium text-red-700 dark:text-red-300">
                                        Documento carregado com sucesso
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Proprietários --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Proprietários</label>
                            <flux:button wire:click="adicionarProprietario" size="sm" variant="ghost" icon="plus">Adicionar</flux:button>
                        </div>
                        @foreach($proprietarios as $index => $prop)
                            <div class="mb-2 grid grid-cols-12 gap-2 items-end">
                                <div class="col-span-4">
                                    <flux:input wire:model="proprietarios.{{ $index }}.nome" placeholder="Nome" size="sm" />
                                </div>
                                <div class="col-span-3">
                                    <flux:input wire:model="proprietarios.{{ $index }}.bi" placeholder="BI" size="sm" />
                                </div>
                                <div class="col-span-3">
                                    <flux:input wire:model="proprietarios.{{ $index }}.contacto" placeholder="Contacto" size="sm" />
                                </div>
                                <div class="col-span-2">
                                    @if(count($proprietarios) > 1)
                                        <flux:button wire:click="removerProprietario({{ $index }})" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($errors->any())
                    <div class="mt-4 rounded bg-red-50 p-3 dark:bg-red-900/20">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="$set('showCroquiForm', false)" variant="ghost" wire:loading.attr="disabled" wire:target="ficheiro_pdf">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="guardarCroqui"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="guardarCroqui,ficheiro_pdf"
                        :disabled="$isUploading || !$ficheiro_pdf"
                    >
                        Guardar
                    </flux:button>
                </div>
            </div>
        </div>
        @endif
</div>
