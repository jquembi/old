<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.cartas', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Cartas Municipais</a>
            <span>/</span>
            <span>{{ $registoId ? 'Editar' : 'Nova' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ $registoId ? 'Editar' : 'Nova' }} Carta Municipal</flux:heading>
                <flux:text class="mt-1">Preencha os dados e faça upload dos ficheiros — {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.cartas', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="max-w-3xl">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-4">
                <flux:input wire:model="titulo" label="Título" required />
                <flux:textarea wire:model="descricao" label="Descrição" rows="3" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="ano" label="Ano" maxlength="4" />

                    <flux:select wire:model="estado" label="Estado">
                        <flux:select.option value="rascunho">Rascunho</flux:select.option>
                        <flux:select.option value="submetido">Submetido</flux:select.option>
                        <flux:select.option value="aprovado">Aprovado</flux:select.option>
                        <flux:select.option value="rejeitado">Rejeitado</flux:select.option>
                    </flux:select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ficheiros</label>
                    <input type="file" wire:model="ficheiros" multiple
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-amber-900/30 dark:file:text-amber-300" />

                    <div wire:loading wire:target="ficheiros" class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A carregar ficheiros...
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-2 dark:bg-zinc-700">
                            <div class="bg-amber-600 h-2 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 text-right">{{ $uploadProgress }}%</div>
                    </div>

                    <div wire:loading.remove wire:target="ficheiros">
                        @if(count($ficheiros) > 0)
                            <div class="mt-3 flex items-center gap-2 p-3 rounded bg-amber-50 dark:bg-amber-900/20">
                                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-medium text-amber-700 dark:text-amber-300">{{ count($ficheiros) }} ficheiro(s) carregado(s) com sucesso</span>
                            </div>
                        @endif
                    </div>
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
                <a href="{{ route('municipio.cartas', $municipio) }}" wire:navigate>
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
    </div>
</div>
