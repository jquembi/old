<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
            <span>/</span>
            <span>Ambiente de Trabalho</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Ambiente de Trabalho</flux:heading>
                <flux:text class="mt-1">Ficheiros QGIS, DWG e outros formatos de trabalho — {{ $municipio->nome }}</flux:text>
            </div>
            @if($podeEditar)
            <a href="{{ route('municipio.operacoes.ambiente-trabalho.enviar', $municipio) }}" wire:navigate>
                <flux:button variant="primary" icon="arrow-up-tray">Enviar Arquivo</flux:button>
            </a>
        @endif
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px]">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por nome do ficheiro..." icon="magnifying-glass" />
        </div>
        <div class="w-48">
            <flux:select wire:model.live="filtroTipo">
                <flux:select.option value="">Todos os tipos</flux:select.option>
                <flux:select.option value="qgis">QGIS</flux:select.option>
                <flux:select.option value="dwg">AutoCAD (DWG)</flux:select.option>
                <flux:select.option value="shp">Shapefile</flux:select.option>
                <flux:select.option value="geojson">GeoJSON</flux:select.option>
                <flux:select.option value="outro">Outro</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Tabela de Arquivos de Trabalho --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ficheiro</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Enviado por</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Actualização</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acções</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse($arquivos as $arquivo)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                @php
                                    $iconColor = match($arquivo->tipo) {
                                        'qgis' => 'text-red-500',
                                        'dwg' => 'text-orange-500',
                                        'shp' => 'text-purple-500',
                                        'geojson' => 'text-blue-500',
                                        default => 'text-zinc-400',
                                    };
                                @endphp
                                <svg class="h-5 w-5 {{ $iconColor }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <div>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $arquivo->titulo ?? $arquivo->ficheiro_nome }}</span>
                                    @if($arquivo->titulo)
                                        <div class="text-xs text-zinc-500">{{ $arquivo->ficheiro_nome }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $arquivo->tipo === 'qgis' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                {{ $arquivo->tipo === 'dwg' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' : '' }}
                                {{ $arquivo->tipo === 'shp' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400' : '' }}
                                {{ $arquivo->tipo === 'geojson' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                {{ !in_array($arquivo->tipo, ['qgis','dwg','shp','geojson']) ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                            ">
                                {{ strtoupper($arquivo->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $arquivo->enviadoPor?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($arquivo->estaEmUso())
                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                    Em uso por {{ $arquivo->emUsoPor?->name ?? '?' }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Disponível
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $arquivo->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                {{-- Abrir / Descarregar --}}
                                <a href="{{ route('storage.serve', $arquivo->ficheiro_caminho) }}?download=1"
                                   class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition"
                                   title="Abrir ficheiro">
                                    <flux:icon.arrow-down-tray class="w-3.5 h-3.5" />
                                    Abrir
                                </a>

                                @if($podeEditar)
                                    @if(!$arquivo->estaEmUso() || $arquivo->em_uso_por === auth()->id())
                                        {{-- Trabalhar (bloquear + baixar) --}}
                                        <button wire:click="trabalhar({{ $arquivo->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-200 dark:bg-sky-900/30 dark:text-sky-300 dark:hover:bg-sky-800 transition"
                                            title="Marcar como em uso e trabalhar">
                                            <flux:icon.wrench class="w-3.5 h-3.5" />
                                            Trabalhar
                                        </button>
                                    @endif

                                    @if($arquivo->estaEmUso() && $arquivo->em_uso_por === auth()->id())
                                        {{-- Liberar lock --}}
                                        <button wire:click="liberar({{ $arquivo->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-800 transition"
                                            title="Liberar arquivo">
                                            <flux:icon.lock-open class="w-3.5 h-3.5" />
                                            Liberar
                                        </button>
                                    @endif

                                    {{-- Substituir versão --}}
                                    @if(!$arquivo->estaEmUso() || $arquivo->em_uso_por === auth()->id())
                                        <button wire:click="abrirSubstituir({{ $arquivo->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-800 transition"
                                            title="Enviar nova versão">
                                            <flux:icon.arrow-path class="w-3.5 h-3.5" />
                                            Substituir
                                        </button>
                                    @endif

                                    @if($podeEliminar)
                                        <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este arquivo para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $arquivo->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                @endif
                            </div>

                            {{-- Formulário de substituição inline --}}
                            @if($substituirId === $arquivo->id)
                                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-left dark:border-amber-800 dark:bg-amber-900/20">
                                    <p class="text-xs font-medium text-amber-700 dark:text-amber-400 mb-2">Enviar nova versão de "{{ $arquivo->ficheiro_nome }}"</p>
                                    <input type="file" wire:model="substituirFicheiro"
                                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-amber-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-amber-700 hover:file:bg-amber-200 dark:file:bg-amber-900/30 dark:file:text-amber-300" />
                                    <div wire:loading wire:target="substituirFicheiro" class="mt-1 flex items-center gap-2 text-xs text-amber-600">
                                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        A carregar...
                                    </div>
                                    @error('substituirFicheiro') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <div class="mt-2 flex gap-2">
                                        <flux:button wire:click="substituirVersao" size="sm" variant="primary" wire:loading.attr="disabled" wire:target="substituirVersao,substituirFicheiro">Enviar</flux:button>
                                        <flux:button wire:click="cancelarSubstituir" size="sm" variant="ghost">Cancelar</flux:button>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.folder-open class="w-8 h-8 text-zinc-300" />
                                <p>Nenhum arquivo de trabalho encontrado.</p>
                                @if($podeEditar)
                                    <a href="{{ route('municipio.operacoes.ambiente-trabalho.enviar', $municipio) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="arrow-up-tray">Enviar primeiro arquivo</flux:button></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $arquivos->links() }}</div>
</div>
