<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.instrumentos-urbanisticos', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Instrumentos Urbanísticos</a>
            <span>/</span>
            <span>Projectos de Loteamentos</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Projectos de Loteamentos</flux:heading>
                <flux:text class="mt-1">{{ $municipio->nome }} — Loteamentos aprovados, concluídos e em curso</flux:text>
            </div>
            @if($podeEditar)
                <a href="{{ route('municipio.loteamento.criar', $municipio) }}" wire:navigate>
                    <flux:button variant="primary" icon="plus">Novo Loteamento</flux:button>
                </a>
            @endif
        </div>
    </div>

    {{-- Tabs de estado --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <button wire:click="setFiltroEstado('')"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors
            {{ !$filtroEstado ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
            Todos
            <span class="rounded-full bg-white/20 dark:bg-black/20 px-1.5 py-0.5 text-xs">{{ $contagens['aprovados'] + $contagens['concluidos'] + $contagens['em_curso'] }}</span>
        </button>
        <button wire:click="setFiltroEstado('aprovado')"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors
            {{ $filtroEstado === 'aprovado' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400' }}">
            Aprovados
            <span class="rounded-full bg-white/20 dark:bg-black/20 px-1.5 py-0.5 text-xs">{{ $contagens['aprovados'] }}</span>
        </button>
        <button wire:click="setFiltroEstado('concluido')"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors
            {{ $filtroEstado === 'concluido' ? 'bg-zinc-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
            Concluídos
            <span class="rounded-full bg-white/20 dark:bg-black/20 px-1.5 py-0.5 text-xs">{{ $contagens['concluidos'] }}</span>
        </button>
        <button wire:click="setFiltroEstado('em_curso')"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors
            {{ $filtroEstado === 'em_curso' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400' }}">
            Em Curso
            <span class="rounded-full bg-white/20 dark:bg-black/20 px-1.5 py-0.5 text-xs">{{ $contagens['em_curso'] }}</span>
        </button>
    </div>

    {{-- Pesquisa --}}
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar loteamentos..." icon="magnifying-glass" />
    </div>

    {{-- Tabela --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Localização</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Ficheiros</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Criado por</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Data</th>
                    @if($podeEditar)
                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase">Acções</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                @forelse($loteamentos as $lot)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $lot->nome }}</div>
                            @if($lot->descricao)
                                <div class="text-xs text-zinc-500 mt-0.5">{{ Str::limit($lot->descricao, 80) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $lot->estado === 'aprovado' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                {{ $lot->estado === 'em_curso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                {{ $lot->estado === 'concluido' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                            ">
                                {{ str_replace('_', ' ', ucfirst($lot->estado)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $lot->localizacao ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($lot->ficheiros->count())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($lot->ficheiros as $f)
                                        <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank"
                                           class="inline-flex items-center gap-1 rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                            <flux:icon.arrow-down-tray class="w-3 h-3" />
                                            {{ Str::limit($f->nome_original, 20) }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $lot->criadoPor?->name }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500">{{ $lot->created_at->format('d/m/Y') }}</td>
                        @if($podeEditar)
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('municipio.loteamento.editar', [$municipio, $lot->id]) }}" wire:navigate><flux:button size="sm" variant="ghost" icon="pencil" /></a>
                                @if($podeEliminar)
                                    <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar este loteamento para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $lot->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $podeEditar ? 7 : 6 }}" class="px-4 py-8 text-center text-sm text-zinc-500">
                            Nenhum loteamento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $loteamentos->links() }}</div>

    {{-- Modal Form --}}

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="$set('showForm', false)" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
            </div>
        </div>
    </div>
    @endif
</div>
