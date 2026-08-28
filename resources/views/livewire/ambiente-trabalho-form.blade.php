<div>
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operações Urbanísticas</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes.ambiente-trabalho', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Ambiente de Trabalho</a>
            <span>/</span>
            <span>Enviar Arquivo</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Enviar Arquivo de Trabalho</flux:heading>
                <flux:text class="mt-1">Ficheiros QGIS, DWG, Shapefile e outros — {{ $municipio->nome }}</flux:text>
            </div>
            <a href="{{ route('municipio.operacoes.ambiente-trabalho', $municipio) }}" wire:navigate>
                <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
            </a>
        </div>
    </div>

    <div class="max-w-3xl">
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-6 dark:border-sky-800 dark:bg-sky-900/20">
            <div class="space-y-4">
                <flux:input wire:model="uploadTitulo" label="Título" placeholder="Ex: Base cartográfica do município" />

                <flux:select wire:model="uploadTipo" label="Tipo de arquivo">
                    <flux:select.option value="qgis">QGIS (.qgs, .qgz)</flux:select.option>
                    <flux:select.option value="dwg">AutoCAD (.dwg, .dxf)</flux:select.option>
                    <flux:select.option value="shp">Shapefile (.shp, .zip)</flux:select.option>
                    <flux:select.option value="geojson">GeoJSON (.geojson, .json)</flux:select.option>
                    <flux:select.option value="outro">Outro</flux:select.option>
                </flux:select>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Arquivo
                        <span class="ml-1 text-xs font-normal text-zinc-400">(máx. 200MB)</span>
                    </label>
                    <input type="file" wire:model="uploadFicheiro"
                        class="block w-full text-sm text-zinc-500 file:mr-4 file:rounded file:border-0 file:bg-sky-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-sky-700 hover:file:bg-sky-200 dark:file:bg-sky-900/30 dark:file:text-sky-300" />

                    <div wire:loading wire:target="uploadFicheiro" class="mt-3 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-sky-600 dark:text-sky-400">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            A carregar ficheiro...
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-2 dark:bg-zinc-700">
                            <div class="bg-sky-600 h-2 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 text-right">{{ $uploadProgress }}%</div>
                    </div>

                    <div wire:loading.remove wire:target="uploadFicheiro">
                        @if($uploadFicheiro)
                            <div class="mt-3 flex items-center gap-2 p-3 rounded bg-sky-100 dark:bg-sky-900/30">
                                <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-medium text-sky-700 dark:text-sky-300">Ficheiro carregado com sucesso</span>
                            </div>
                        @endif
                    </div>

                    @error('uploadFicheiro') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                <a href="{{ route('municipio.operacoes.ambiente-trabalho', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="uploadFicheiro">Cancelar</flux:button>
                </a>
                <flux:button wire:click="enviar" variant="primary" icon="arrow-up-tray"
                    wire:loading.attr="disabled" wire:target="enviar,uploadFicheiro"
                    :disabled="$isUploading || !$uploadFicheiro">
                    <span wire:loading.remove wire:target="enviar">Enviar</span>
                    <span wire:loading wire:target="enviar" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        A enviar...
                    </span>
                </flux:button>
            </div>
        </div>
    </div>
</div>
