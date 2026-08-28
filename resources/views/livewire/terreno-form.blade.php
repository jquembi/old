<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.terrenos-ociosos', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Terrenos Ociosos</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Novo' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Novo' }} Terreno Ocioso</flux:heading>
                <flux:text class="mt-1">Preencha o título e faça upload do documento em PDF ou Word — {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.operacoes.terrenos-ociosos', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="max-w-3xl space-y-6">
        {{-- Título e PDF --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:input wire:model="titulo" label="Título" placeholder="Ex: Terreno Ocioso - Zona Industrial" required />
                <flux:input wire:model="ano" label="Ano" maxlength="4" />

                <flux:select wire:model="visibilidade" label="Visibilidade no portal">
                    <option value="privado">Privado - apenas consulta por BI/referência</option>
                    <option value="publico">Público - aparece na consulta pública</option>
                </flux:select>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Arquivo PDF ou Word
                        <span class="ml-1 text-xs font-normal text-zinc-400">(máx. 50MB)</span>
                    </label>
                    @if($ficheiroPdfAtualCaminho)
                        <div class="mb-3 flex flex-col gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">PDF atual</p>
                                <p class="mt-1 truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $ficheiroPdfAtualNome ?? basename($ficheiroPdfAtualCaminho) }}</p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('storage.serve', $ficheiroPdfAtualCaminho) }}" target="_blank">
                                    <flux:button size="sm" variant="ghost" icon="eye">Abrir</flux:button>
                                </a>
                                @if(auth()->user()?->podeEnviarParaLixeira())
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        class="text-red-600 dark:text-red-400"
                                        x-on:click.prevent="$dispatch('confirmar', { title: 'Remover PDF', message: 'Deseja remover o PDF associado a este terreno ocioso? Esta ação não pode ser desfeita.', action: 'removerFicheiroPdfAtual', component: $wire.$id, params: [], variant: 'danger', confirmLabel: 'Remover' })"
                                    />
                                @endif
                            </div>
                        </div>
                    @endif
                    <input type="file" wire:model="ficheiro_pdf" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-900/30 dark:file:text-red-300 cursor-pointer" />

                    <div wire:loading wire:target="ficheiro_pdf" class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A carregar documento...
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-1.5 dark:bg-zinc-700">
                            <div class="bg-red-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $this->uploadProgress }}%"></div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 text-right">{{ $this->uploadProgress }}%</div>
                    </div>

                    <div wire:loading.remove wire:target="ficheiro_pdf">
                        @if($ficheiro_pdf)
                            <div class="mt-3 flex items-center gap-2 p-3 rounded bg-red-50 dark:bg-red-900/20">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-medium text-red-700 dark:text-red-300">Documento carregado com sucesso</span>
                            </div>
                        @endif
                    </div>

                    @error('ficheiro_pdf') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <h4 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Pagamento RUP</h4>
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300 mb-3">
                        <input type="checkbox" wire:model="rup_pago" class="rounded border-zinc-300 text-red-600 focus:ring-red-500" />
                        RUP pago para este terreno
                    </label>
                    <flux:input wire:model="rup_referencia" label="Referência/Comprovativo RUP" placeholder="Ex: RUP-2026-000123" />
                    @error('rup_referencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    <p class="mt-2 text-xs text-zinc-400">O download só será liberado após homologação do administrador.</p>
                </div>
            </div>
        </div>

        {{-- Dados do Proprietário --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Dados do Proprietário</h3>
                <flux:button wire:click="adicionarProprietario" size="sm" variant="ghost" icon="plus">Adicionar</flux:button>
            </div>
            <div class="space-y-4">
                @foreach($proprietarios as $index => $prop)
                    <div class="relative rounded-lg border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        @if(count($proprietarios) > 1)
                            <button wire:click="removerProprietario({{ $index }})" type="button"
                                class="absolute top-2 right-2 text-red-400 hover:text-red-600 transition" title="Remover">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <flux:input wire:model="proprietarios.{{ $index }}.nome" label="Nome" placeholder="Nome completo" required />
                            <flux:input wire:model="proprietarios.{{ $index }}.bi" label="BI / NIF" placeholder="Nº do documento" />
                            <flux:input wire:model="proprietarios.{{ $index }}.contacto" label="Contacto" placeholder="Telefone" />
                        </div>
                        @error("proprietarios.{$index}.nome") <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Coordenadas UTM --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Coordenadas UTM</h3>
                <flux:button wire:click="adicionarCoordenada" size="sm" variant="ghost" icon="plus">Adicionar Ponto</flux:button>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Zona UTM</label>
                    <select wire:model="utm_zone" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                        <option value="32">32 — Litoral oeste (Luanda)</option>
                        <option value="33">33 — Região central (Huíla)</option>
                        <option value="34">34 — Região leste</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Hemisfério</label>
                    <select wire:model="utm_hemisphere" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                        <option value="S">Sul (S)</option>
                        <option value="N">Norte (N)</option>
                    </select>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($coordenadas as $index => $coord)
                    <div class="relative flex items-end gap-3">
                        <span class="pb-2 text-xs font-medium text-zinc-400 w-6 shrink-0">{{ $index + 1 }}.</span>
                        <div class="flex-1">
                            <flux:input wire:model="coordenadas.{{ $index }}.easting" label="{{ $index === 0 ? 'Easting (m)' : '' }}" placeholder="Ex: 456789" type="number" step="any" />
                        </div>
                        <div class="flex-1">
                            <flux:input wire:model="coordenadas.{{ $index }}.northing" label="{{ $index === 0 ? 'Northing (m)' : '' }}" placeholder="Ex: 9012345" type="number" step="any" />
                        </div>
                        @if(count($coordenadas) > 1)
                            <button wire:click="removerCoordenada({{ $index }})" type="button"
                                class="pb-2 text-red-400 hover:text-red-600 transition" title="Remover ponto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                    </div>
                    @error("coordenadas.{$index}.easting") <span class="text-red-500 text-xs ml-9">{{ $message }}</span> @enderror
                    @error("coordenadas.{$index}.northing") <span class="text-red-500 text-xs ml-9">{{ $message }}</span> @enderror
                @endforeach
            </div>
            <p class="mt-3 text-xs text-zinc-400">Datum WGS84 — UTM {{ $utm_zone }}{{ $utm_hemisphere }} — Easting e Northing em metros</p>
        </div>

        {{-- Erros e botões --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            @if($errors->any())
                <div class="mb-4 rounded bg-red-50 p-3 dark:bg-red-900/20">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div class="flex justify-end gap-3">
                <a href="{{ route('municipio.operacoes.terrenos-ociosos', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,ficheiro_pdf">
                    <span wire:loading.remove wire:target="guardar">{{ $registoId ? 'Actualizar' : 'Guardar' }}</span>
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
</div>
