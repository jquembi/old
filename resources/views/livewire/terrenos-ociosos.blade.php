<div>
    {{-- SECTION: LISTA — Terrenos Ociosos --}}
    <div class="px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
        <div>
            <nav class="breadcrumb mb-3" aria-label="Caminho de navegação">
                <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <a href="{{ route('municipio.show', $municipio) }}" wire:navigate>{{ $municipio->nome }}</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <a href="{{ route('municipio.operacoes', $municipio) }}" wire:navigate>Operações Urbanísticas</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span class="text-zinc-600 dark:text-zinc-300 font-medium">Terrenos Ociosos</span>
            </nav>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">Terrenos Ociosos</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Documentos PDF de terrenos ociosos — {{ $municipio->nome }}</p>
                </div>
                @if($podeEditar)
                    <a href="{{ route('municipio.operacoes.terreno.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus">Novo Terreno</flux:button>
                    </a>
                @endif
            </div>
        </div>

        <div>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por título ou nome do ficheiro..." icon="magnifying-glass" />
        </div>

        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Documento PDF</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">RUP / Homologação</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Criado por</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Data</th>
                        @if($podeEditar)
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Acções</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($croquis as $croqui)
                        <tr class="transition-colors hover:bg-red-50/40 dark:hover:bg-red-950/10">
                            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $croqui->titulo ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($croqui->ficheiro_pdf_caminho)
                                    @if($croqui->podeBaixar(auth()->user()))
                                        <a href="{{ route('storage.serve', ['path' => $croqui->ficheiro_pdf_caminho, 'download' => 1]) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:underline dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            {{ $croqui->ficheiro_pdf_nome ?? 'Descarregar PDF' }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.062 17h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.33 14c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Bloqueado até homologação
                                        </span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-col gap-1">
                                    @if($croqui->rup_pago)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 w-fit">RUP pago</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 w-fit">RUP pendente</span>
                                    @endif

                                    @if($croqui->download_liberado)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 w-fit">Homologado</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 w-fit">Aguardando homologação</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $croqui->criadoPor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $croqui->created_at->format('d/m/Y H:i') }}</td>
                            @if($podeEditar)
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    @if($podeHomologar)
                                        @if(!$croqui->download_liberado)
                                            <flux:button wire:click="homologarDownload({{ $croqui->id }})" size="sm" variant="ghost" icon="check-badge" class="text-blue-600" title="Homologar e liberar download" />
                                        @else
                                            <flux:button wire:click="revogarDownload({{ $croqui->id }})" size="sm" variant="ghost" icon="lock-closed" class="text-amber-600" title="Revogar homologação" />
                                        @endif
                                    @endif
                                    <a href="{{ route('municipio.operacoes.terreno.editar', [$municipio, $croqui->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                                    @if($podeEliminar)
                                        <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este terreno para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $croqui->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $podeEditar ? 6 : 5 }}" class="px-4 py-14 text-center">
                                <svg class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nenhum terreno ocioso encontrado</p>
                                @if(isset($search) && $search)
                                    <button wire:click="$set('search', '')" class="mt-2 text-xs text-red-600 dark:text-red-400 hover:underline">Limpar pesquisa</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($croquis->hasPages())
            <div class="mt-4">{{ $croquis->links() }}</div>
        @endif
    </div>
</div>
