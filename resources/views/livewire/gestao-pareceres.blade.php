<div>
    {{-- ----------------------------------------------------------------
         LISTA: Pareceres INOTU
    ------------------------------------------------------------------- --}}
    <div>
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
                <span>/</span>
                <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
                <span>/</span>
                <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
                <span>/</span>
                <span>Pareceres INOTU</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">Pareceres do INOTU</flux:heading>
                    <flux:text class="mt-1">Pareceres tecnicos para croquis, terrenos, loteamentos e demais servicos territoriais — {{ $municipio->nome }}</flux:text>
                </div>
                @if($podeEditar)
                    <a href="{{ route('municipio.operacoes.parecer.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus">Novo Parecer</flux:button>
                    </a>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por título ou nome do ficheiro..." icon="magnifying-glass" />
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Servico</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Area</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">PDF</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Criado por</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                        @if($podeEditar)
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acções</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($pareceres as $parecer)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $parecer->titulo }}
                                @if($parecer->classificacao_area || $parecer->uso_solo)
                                    <div class="mt-1 text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                        {{ collect([$parecer->classificacao_area, $parecer->uso_solo])->filter()->join(' · ') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $parecer->servicoLabel() }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $parecer->area_reservada === null ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' : ($parecer->area_reservada ? 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300') }}">
                                    {{ $parecer->areaReservadaLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($parecer->ficheiro_caminho)
                                    <a href="{{ route('storage.serve', $parecer->ficheiro_caminho) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-blue-600 hover:underline dark:text-blue-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ $parecer->ficheiro_nome ?? 'Ver PDF' }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $parecer->criadoPor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $parecer->created_at->format('d/m/Y H:i') }}</td>
                            @if($podeEditar)
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('municipio.operacoes.parecer.editar', [$municipio, $parecer->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                                    @if($podeEliminar)
                                        <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este parecer para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $parecer->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $podeEditar ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-zinc-500">Nenhum parecer encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $pareceres->links() }}</div>
    </div>
</div>
