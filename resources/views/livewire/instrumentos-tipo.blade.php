<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ $categoriaRoute }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $categoriaNome }}</a>
            <span>/</span>
            <span>{{ $tipo->nome }}</span>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">{{ $tipo->nome }}</flux:heading>
                <flux:text class="mt-1">{{ $municipio->nome }} - {{ $categoriaNome }}</flux:text>
            </div>
            @if($podeEditar)
                <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $tipoSlug]) }}" wire:navigate>
                    <flux:button variant="primary" icon="plus">Novo registo</flux:button>
                </a>
            @endif
        </div>
    </div>

    <div class="mb-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por titulo ou descricao..." icon="magnifying-glass" />
        <div class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
            {{ $instrumentos->total() }} {{ $instrumentos->total() === 1 ? 'registo' : 'registos' }}
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Titulo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ano</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ficheiros</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Criado por</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                    @if($podeEditar)
                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acoes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse($instrumentos as $inst)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $inst->titulo }}</div>
                            @if($inst->descricao)
                                <div class="text-xs text-zinc-500 mt-0.5">{{ Str::limit($inst->descricao, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $inst->ano ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $inst->estado === 'aprovado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400' : '' }}
                                {{ $inst->estado === 'submetido' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                {{ $inst->estado === 'rascunho' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                                {{ $inst->estado === 'rejeitado' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                            ">
                                {{ ucfirst(str_replace('_', ' ', $inst->estado)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($inst->ficheiros->count())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($inst->ficheiros as $f)
                                        <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                           class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                            <flux:icon.arrow-down-tray class="w-3 h-3" />
                                            {{ Str::limit($f->nome_original, 20) }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $inst->criadoPor?->name }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $inst->created_at->format('d/m/Y') }}</td>
                        @if($podeEditar)
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('municipio.instrumentos.tipo.editar', [$municipio, $tipoSlug, $inst->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                                @if($podeEliminar)
                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este registo para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $inst->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $podeEditar ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-zinc-500">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon.folder-open class="h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                                <span>Nenhum registo encontrado para {{ $tipo->nome }}.</span>
                                @if($podeEditar)
                                    <a href="{{ route('municipio.instrumentos.tipo.criar', [$municipio, $tipoSlug]) }}" wire:navigate>
                                        <flux:button size="sm" variant="subtle" icon="plus">Criar registo</flux:button>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $instrumentos->links() }}</div>
</div>
