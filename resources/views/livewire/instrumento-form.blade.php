<div>
    <div class="mb-6">
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ $categoriaRoute }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $categoriaNome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $tipo->nome }}</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Novo' }}</span>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Novo' }} - {{ $tipo->nome }}</flux:heading>
                <flux:text class="mt-1">Dados do instrumento e ficheiros anexos - {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-5">
                <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                    Ao escolher ficheiros, o sistema preenche automaticamente o titulo com o nome do primeiro ficheiro e usa o ano actual quando o campo estiver vazio.
                </div>

                <div class="grid gap-4 lg:grid-cols-[2fr_8rem_12rem]">
                    <flux:input wire:model="titulo" label="Titulo" placeholder="Ex: {{ $tipo->nome }} - {{ $municipio->nome }}" required />
                    <flux:input wire:model="ano" label="Ano" maxlength="4" />
                    <flux:select wire:model="estado" label="Estado">
                        <flux:select.option value="rascunho">Rascunho</flux:select.option>
                        <flux:select.option value="submetido">Submetido</flux:select.option>
                        <flux:select.option value="aprovado">Aprovado</flux:select.option>
                        <flux:select.option value="rejeitado">Rejeitado</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="descricao" label="Descricao" rows="4" placeholder="Resumo, cobertura territorial, observacoes tecnicas ou enquadramento do instrumento." />

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Ficheiros</label>
                    <input type="file" wire:model="ficheiros" multiple
                        class="block w-full rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-4 text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-zinc-200 file:px-4 file:py-2 file:text-sm file:font-semibold hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:file:bg-zinc-800 dark:file:text-zinc-300" />

                    <div wire:loading wire:target="ficheiros" class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-400">
                            <span>A carregar ficheiros...</span>
                            <span>{{ $uploadProgress }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div class="h-2 rounded-full bg-zinc-700 transition-all duration-300 dark:bg-zinc-300" style="width: {{ $uploadProgress }}%"></div>
                        </div>
                    </div>

                    <div wire:loading.remove wire:target="ficheiros">
                        @if(count($ficheiros) > 0)
                            <div class="mt-3 space-y-2 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ count($ficheiros) }} ficheiro(s) seleccionados</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($ficheiros as $ficheiro)
                                        <span class="rounded-full bg-white px-2.5 py-1 text-xs text-zinc-600 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">
                                            {{ $ficheiro->getClientOriginalName() }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="mt-4 rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <a href="{{ route('municipio.instrumentos.tipo', [$municipio, $tipoSlug]) }}" wire:navigate>
                    <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="ficheiros">Cancelar</flux:button>
                </a>
                <flux:button wire:click="guardar" variant="primary" wire:loading.attr="disabled" wire:target="guardar,ficheiros" :disabled="$isUploading || (count($ficheiros) === 0 && !$registoId)">
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

        <aside class="rounded-xl border border-zinc-200 bg-white p-5 text-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="font-semibold text-zinc-900 dark:text-zinc-100">Resumo</div>
            <dl class="mt-4 space-y-3 text-zinc-600 dark:text-zinc-300">
                <div>
                    <dt class="text-xs uppercase text-zinc-400">Municipio</dt>
                    <dd>{{ $municipio->nome }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-zinc-400">Categoria</dt>
                    <dd>{{ $categoriaNome ?: 'Instrumento' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-zinc-400">Tipo</dt>
                    <dd>{{ $tipo->nome }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-zinc-400">Ficheiros novos</dt>
                    <dd>{{ count($ficheiros) }}</dd>
                </div>
            </dl>
        </aside>
    </div>
</div>
