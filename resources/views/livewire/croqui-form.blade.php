<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.croquis-elaborados', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Croquis Elaborados</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Novo' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Novo' }} Croqui Elaborado</flux:heading>
                <flux:text class="mt-1">Preencha o título e faça upload do croqui e da memoria descritiva em PDF ou Word — {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.operacoes.croquis-elaborados', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna Esquerda (2/3) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Título e documentos --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:input wire:model="titulo" label="Título" placeholder="Ex: Croqui da Parcela 001" required />
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
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Documento atual</p>
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
                                        x-on:click.prevent="$dispatch('confirmar', { title: 'Remover documento', message: 'Deseja remover o documento associado a este croqui? Esta ação não pode ser desfeita.', action: 'removerFicheiroPdfAtual', component: $wire.$id, params: [], variant: 'danger', confirmLabel: 'Remover' })"
                                    />
                                @endif
                            </div>
                        </div>
                    @endif
                    <input type="file" wire:model="ficheiro_pdf" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-900/30 dark:file:text-violet-300"
                        x-init="$wire.$watch('ficheiro_pdf', function(value) {
                            if (value && typeof window.extrairDadosFicheiroPdf === 'function') {
                                window.extrairDadosFicheiroPdf($el.querySelector('input[type=file]'), $wire.el.id);
                            }
                        })" />

                    <div wire:loading wire:target="ficheiro_pdf" class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-violet-600 dark:text-violet-400">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A carregar documento...
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-2 dark:bg-zinc-700">
                            <div class="bg-violet-600 h-2 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 text-right">{{ $uploadProgress }}%</div>
                    </div>

                    @error('ficheiro_pdf') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Memoria descritiva do croqui (PDF ou Word)
                        <span class="ml-1 text-xs font-normal text-zinc-400">(max. 50MB)</span>
                    </label>
                    <p class="mb-3 text-xs text-zinc-400">Ao carregar este documento, o sistema le o texto da memoria e tenta preencher automaticamente os dados do croqui.</p>
                    @if($memoriaDescritivaAtualCaminho)
                        <div class="mb-3 flex flex-col gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Memoria atual</p>
                                <p class="mt-1 truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $memoriaDescritivaAtualNome ?? basename($memoriaDescritivaAtualCaminho) }}</p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('storage.serve', $memoriaDescritivaAtualCaminho) }}" target="_blank">
                                    <flux:button size="sm" variant="ghost" icon="eye">Abrir</flux:button>
                                </a>
                                @if(auth()->user()?->podeEnviarParaLixeira())
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        class="text-red-600 dark:text-red-400"
                                        x-on:click.prevent="$dispatch('confirmar', { title: 'Remover memoria', message: 'Deseja remover a memoria descritiva associada a este croqui? Esta acao nao pode ser desfeita.', action: 'removerMemoriaDescritivaAtual', component: $wire.$id, params: [], variant: 'danger', confirmLabel: 'Remover' })"
                                    />
                                @endif
                            </div>
                        </div>
                    @endif
                    <input type="file" wire:model="memoria_descritiva" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300"
                        x-init="$wire.$watch('memoria_descritiva', function(value) {
                            if (value && typeof window.extrairDadosMemoriaDescritiva === 'function') {
                                window.extrairDadosMemoriaDescritiva($el.querySelector('input[type=file]'), $wire.el.id);
                            }
                        })" />

                    <div wire:loading wire:target="memoria_descritiva" class="mt-3 flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A carregar memoria...
                    </div>

                    @error('memoria_descritiva') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/10">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Pagamento RUP gerido na lista</p>
                            <p class="mt-0.5 text-xs text-amber-600 dark:text-amber-400">Após publicação pelo administrador, o pagamento RUP é registado directamente na lista de croquis elaborados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                {{-- Micro-Localização (imagem de referência — exclusivo croqui) --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Micro-Localização</h3>
                            <p class="text-xs text-zinc-400 mb-3">Imagem de referência que aparece no retângulo superior direito do croqui impresso. Formatos: JPG, PNG ou WebP (máx. 10 MB).</p>
                            <input type="file" wire:model="micro_localizacao" accept="image/jpeg,image/png,image/webp"
                                class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-300" />

                            <div wire:loading wire:target="micro_localizacao" class="mt-3 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                A carregar imagem...
                            </div>

                            @error('micro_localizacao') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Pré-visualização --}}
                        @if($microAtualCaminho)
                            <div class="flex-shrink-0">
                                <p class="text-xs text-zinc-400 mb-2">Imagem atual:</p>
                                <img src="{{ Storage::url($microAtualCaminho) }}" alt="Micro-Localização" class="h-28 w-40 object-cover rounded border border-zinc-200 dark:border-zinc-700" />
                                <div class="mt-2 flex gap-2">
                                    <a href="{{ route('storage.serve', $microAtualCaminho) }}" target="_blank">
                                        <flux:button size="sm" variant="ghost" icon="eye">Abrir</flux:button>
                                    </a>
                                    @if(auth()->user()?->podeEnviarParaLixeira())
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="trash"
                                            class="text-red-600 dark:text-red-400"
                                            x-on:click.prevent="$dispatch('confirmar', { title: 'Remover imagem', message: 'Deseja remover a micro-localização associada a este croqui? Esta ação não pode ser desfeita.', action: 'removerMicroLocalizacaoAtual', component: $wire.$id, params: [], variant: 'danger', confirmLabel: 'Remover' })"
                                        />
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Coordenadas GMS + UTM --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Coordenadas</h3>
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

                    {{-- Cabeçalho da tabela --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-zinc-50 dark:bg-zinc-800">
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-2 text-center text-zinc-500 w-8" rowspan="2">Ponto</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-600 dark:text-zinc-300" colspan="2">LATITUDE (GMS e UTM)</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-600 dark:text-zinc-300" colspan="2">LONGITUDE (GMS e UTM)</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-2 text-center text-zinc-500 w-8" rowspan="2"></th>
                                </tr>
                                <tr class="bg-zinc-50 dark:bg-zinc-800">
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-500">GMS</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-500">UTM Y (Northing)</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-500">GMS</th>
                                    <th class="border border-zinc-200 dark:border-zinc-700 px-2 py-1 text-center text-zinc-500">UTM X (Easting)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coordenadas as $index => $coord)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-2 py-2 text-center font-medium text-zinc-400">{{ $index + 1 }}</td>
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-1 py-1">
                                            <input wire:model="coordenadas.{{ $index }}.lat_gms" type="text" placeholder="Ex: 08°58'00&quot;S"
                                                class="w-full rounded border-0 bg-transparent px-2 py-1 text-xs text-zinc-700 dark:text-zinc-200 placeholder-zinc-300 focus:ring-1 focus:ring-violet-400 min-w-[120px]" />
                                        </td>
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-1 py-1">
                                            <input wire:model="coordenadas.{{ $index }}.lat_utm" type="number" step="any" min="0" max="10000000" placeholder="9012345"
                                                class="w-full rounded border-0 bg-transparent px-2 py-1 text-xs text-zinc-700 dark:text-zinc-200 placeholder-zinc-300 focus:ring-1 focus:ring-violet-400 min-w-[100px]" />
                                        </td>
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-1 py-1">
                                            <input wire:model="coordenadas.{{ $index }}.lon_gms" type="text" placeholder="Ex: 013°30'00&quot;E"
                                                class="w-full rounded border-0 bg-transparent px-2 py-1 text-xs text-zinc-700 dark:text-zinc-200 placeholder-zinc-300 focus:ring-1 focus:ring-violet-400 min-w-[120px]" />
                                        </td>
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-1 py-1">
                                            <input wire:model="coordenadas.{{ $index }}.lon_utm" type="number" step="any" min="100000" max="999999" placeholder="456789"
                                                class="w-full rounded border-0 bg-transparent px-2 py-1 text-xs text-zinc-700 dark:text-zinc-200 placeholder-zinc-300 focus:ring-1 focus:ring-violet-400 min-w-[100px]" />
                                        </td>
                                        <td class="border border-zinc-200 dark:border-zinc-700 px-1 py-1 text-center">
                                            @if(count($coordenadas) > 1)
                                                <button wire:click="removerCoordenada({{ $index }})" type="button"
                                                    class="text-red-400 hover:text-red-600 transition" title="Remover ponto">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @error("coordenadas.{$index}.lat_utm") <tr><td colspan="6"><span class="text-red-500 text-xs px-2">{{ $message }}</span></td></tr> @enderror
                                    @error("coordenadas.{$index}.lon_utm") <tr><td colspan="6"><span class="text-red-500 text-xs px-2">{{ $message }}</span></td></tr> @enderror
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-zinc-400">Datum WGS84 — UTM {{ $utm_zone }}{{ $utm_hemisphere }} — GMS: Graus/Minutos/Segundos — UTM Y (Northing) e UTM X (Easting) em metros</p>
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
            </div>

            {{-- Coluna Direita (1/3) - Dados da Legenda --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900 sticky top-6">
                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-4">Dados da Legenda</h3>
                    <p class="text-xs text-zinc-400 mb-5">Informações que preenchem a legenda do croqui impresso (bloco inferior direito).</p>
                    <div class="space-y-4">
                        <flux:input wire:model="area" label="Área (m²)" placeholder="Ex: 2000.0000" />
                        <flux:input wire:model="perimetro" label="Perímetro (m)" placeholder="Ex: 180.0000" />
                        <flux:input wire:model="dimensao" label="Dimensão" placeholder="Ex: 40x50" />
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Escala</label>
                            <select wire:model="escala" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                                <option value="1/500">1/500</option>
                                <option value="1/1000">1/1000</option>
                                <option value="1/2500">1/2500</option>
                                <option value="1/5000">1/5000</option>
                                <option value="1/10000">1/10000</option>
                                <option value="1/25000">1/25000</option>
                                <option value="1/50000">1/50000</option>
                            </select>
                        </div>
                        <flux:input wire:model="tecnico_responsavel" label="Técnico Responsável" placeholder="Nome completo do técnico" />
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input wire:model="folha_numero" label="Folha Nº" placeholder="Ex: 01" />
                            <flux:input wire:model="data_folha" label="Mês/Ano" placeholder="Ex: 04/2026" maxlength="7" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Finalidade</label>
                            <select wire:model="finalidade" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                                <option value="A fim de legalizar">A fim de legalizar</option>
                                <option value="A fim de regularizar">A fim de regularizar</option>
                                <option value="A fim de planear">A fim de planear</option>
                                <option value="A fim de subdividir">A fim de subdividir</option>
                                <option value="A fim de remodelar">A fim de remodelar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
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
                <a href="{{ route('municipio.operacoes.croquis-elaborados', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,ficheiro_pdf,memoria_descritiva">
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
