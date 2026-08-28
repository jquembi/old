<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <span>Cartas Municipais</span>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <flux:heading size="xl">Cartas Municipais</flux:heading>
                    <flux:text class="mt-1">Cartas, mapas municipais, pesquisa e download de ficheiros - {{ $municipio->nome }}</flux:text>
                </div>
                @if($podeEditar)
                    <a href="{{ route('municipio.cartas.criar', $municipio) }}" wire:navigate>
                        <flux:button variant="primary" icon="plus">Nova carta</flux:button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($detalhe)
        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <flux:heading size="lg">{{ $detalhe->titulo }}</flux:heading>
                        <flux:text class="mt-1 text-sm">
                            Ano: {{ $detalhe->ano ?? '-' }} &middot;
                            Criado por {{ $detalhe->criadoPor?->name ?? '-' }} em {{ $detalhe->created_at->format('d/m/Y H:i') }}
                        </flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $estadoClass = match ($detalhe->estado) {
                                'aprovado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300',
                                'submetido' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                                'rejeitado' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                            };
                        @endphp
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoClass }}">{{ ucfirst($detalhe->estado) }}</span>
                        <flux:button wire:click="fecharDetalhe" size="sm" variant="ghost" icon="x-mark" />
                    </div>
                </div>
            </div>

            @if($detalhe->descricao)
                <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">
                    <div class="text-xs font-semibold uppercase text-zinc-400">Descricao</div>
                    <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $detalhe->descricao }}</div>
                </div>
            @endif

            <div class="p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">Ficheiros</div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $detalhe->ficheiros->count() }}</span>
                </div>

                @if($detalhe->ficheiros->count())
                    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                        @foreach($detalhe->ficheiros as $f)
                            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                        <flux:icon.document class="h-5 w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $f->nome_original }}</div>
                                        <div class="text-xs text-zinc-500">
                                            {{ strtoupper($f->tipo_ficheiro) }} &middot; {{ number_format($f->tamanho / 1024, 1) }} KB &middot; {{ $f->enviadoPor?->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('storage.serve', $f->caminho) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300">
                                        <flux:icon.arrow-down-tray class="h-4 w-4" />
                                        Download
                                    </a>
                                    @if($podeEliminar)
                                        <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Eliminar este ficheiro?', action: 'eliminarFicheiro', component: $wire.$id, params: [{{ $f->id }}], variant: 'danger' })" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center text-sm text-zinc-500 dark:border-zinc-700">
                        Nenhum ficheiro anexado a esta carta.
                    </div>
                @endif
            </div>

            @if($podeEditar)
                <div class="flex justify-end gap-2 border-t border-zinc-200 p-5 dark:border-zinc-700">
                    <a href="{{ route('municipio.cartas.editar', [$municipio, $detalhe->id]) }}" wire:navigate>
                        <flux:button variant="primary" icon="pencil" size="sm">Editar</flux:button>
                    </a>
                    @if($podeEliminar)
                        <flux:button x-on:click.prevent="$dispatch('confirmar', { title: 'Confirmacao', message: 'Enviar esta carta municipal para a lixeira?', action: 'eliminar', component: $wire.$id, params: [{{ $detalhe->id }}], variant: 'danger' })" variant="danger" icon="trash" size="sm">Eliminar</flux:button>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-center">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por titulo, descricao ou ano..." icon="magnifying-glass" />
            <div class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                {{ $cartas->total() }} {{ $cartas->total() === 1 ? 'carta' : 'cartas' }}
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($cartas as $carta)
                <button wire:click="verDetalhe({{ $carta->id }})" class="group rounded-xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-amber-600">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            <flux:icon.document-text class="h-5 w-5" />
                        </div>
                        @php
                            $estadoClass = match ($carta->estado) {
                                'aprovado' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300',
                                'submetido' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                                'rejeitado' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300',
                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                            };
                        @endphp
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $estadoClass }}">{{ ucfirst($carta->estado) }}</span>
                    </div>
                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $carta->titulo }}</div>
                    @if($carta->descricao)
                        <div class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $carta->descricao }}</div>
                    @endif
                    <div class="mt-4 flex items-center justify-between text-xs text-zinc-500">
                        <span>{{ $carta->ano ?? '-' }}</span>
                        <span class="flex items-center gap-1">
                            <flux:icon.paper-clip class="h-3 w-3" />
                            {{ $carta->ficheiros->count() }}
                        </span>
                    </div>
                </button>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                    <flux:icon.document-text class="mx-auto mb-3 h-9 w-9 text-zinc-300 dark:text-zinc-600" />
                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Nenhuma carta municipal encontrada.</div>
                    @if($podeEditar)
                        <a href="{{ route('municipio.cartas.criar', $municipio) }}" wire:navigate>
                            <flux:button class="mt-3" size="sm" variant="subtle" icon="plus">Criar carta</flux:button>
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <div>{{ $cartas->links() }}</div>
    @endif
</div>
