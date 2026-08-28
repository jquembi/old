<div class="space-y-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('dashboard') }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Dashboard</a>
            <span>/</span>
            <a href="{{ route('municipio.show', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>{{ $municipio->nome }}</a>
            <span>/</span>
            <a href="{{ route('municipio.operacoes', $municipio) }}" class="hover:text-zinc-700 dark:hover:text-zinc-200" wire:navigate>Operacoes Urbanisticas</a>
            <span>/</span>
            <span>IDs de Terreno</span>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <flux:heading size="xl">IDs de Terreno</flux:heading>
                    <flux:text class="mt-1">Consulta reservada por pesquisa e verificacao de novo codigo territorial - {{ $municipio->nome }}</flux:text>
                </div>
                <a href="{{ route('municipio.operacoes', $municipio) }}" wire:navigate>
                    <flux:button variant="ghost" icon="arrow-left">Voltar</flux:button>
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pesquisar terreno</label>
                <input
                    wire:model.defer="pesquisa"
                    wire:keydown.enter="pesquisar"
                    type="search"
                    placeholder="Pesquisar por ID territorial, titulo, proprietario, BI, contacto ou coordenada"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                >
                @error('pesquisa') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="pesquisar" variant="primary" icon="magnifying-glass">Pesquisar</flux:button>
                @if($pesquisaExecutada)
                    <flux:button wire:click="limparPesquisa" variant="ghost" icon="x-mark">Limpar</flux:button>
                @endif
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $pesquisaExecutada ? 'Resultados da pesquisa' : 'IDs de terreno registados' }}
                </h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $pesquisaExecutada ? 'A lista esta filtrada pelo termo pesquisado.' : 'Ultimos 50 IDs territoriais registados neste municipio.' }}
                </p>
            </div>
            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $resultados->count() }} registo(s)
            </span>
        </div>

        @if($resultados->isEmpty())
            <div class="mt-5 rounded-lg border border-zinc-200 p-4 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                Nenhum ID de terreno encontrado.
            </div>
        @else
            <div class="mt-5 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">ID territorial</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Processo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Proprietario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Centro UTM</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @foreach($resultados as $terreno)
                            <tr>
                                <td class="px-4 py-3 font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $terreno->codigo_territorial }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300">{{ $terreno->titulo }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $terreno->proprietarios->pluck('nome')->filter()->join(', ') ?: 'Sem proprietario' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-500">E {{ $terreno->centro_utm_easting }} · N {{ $terreno->centro_utm_northing }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('municipio.operacoes.ids-terreno.detalhe', [$municipio, $terreno]) }}" wire:navigate>
                                        <flux:button size="sm" variant="ghost" icon="eye">Abrir</flux:button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-xl">
                <flux:heading size="lg">Verificar novo ID</flux:heading>
                <flux:text class="mt-1">Gera o codigo territorial pelo centro UTM e verifica sobreposicao com margem de proteccao de 15m.</flux:text>
            </div>
            <div class="grid gap-2 sm:grid-cols-5 lg:min-w-[620px]">
                <select wire:model="idTerrenoZona" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="32">32</option>
                    <option value="33">33</option>
                    <option value="34">34</option>
                </select>
                <select wire:model="idTerrenoHemisferio" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="S">S</option>
                    <option value="N">N</option>
                </select>
                <input wire:model="idTerrenoEasting" type="number" step="0.001" placeholder="Easting" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <input wire:model="idTerrenoNorthing" type="number" step="0.001" placeholder="Northing" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <flux:button wire:click="consultarIdTerreno" variant="primary" icon="shield-check">Verificar</flux:button>
            </div>
        </div>

        @if($idTerrenoResultado)
            <div class="mt-4 rounded-lg border p-4 {{ $idTerrenoResultado['disponivel'] ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300' }}">
                <div class="text-sm font-semibold">{{ $idTerrenoResultado['codigo'] }}</div>
                <div class="mt-1 text-sm">{{ $idTerrenoResultado['mensagem'] }}</div>
            </div>
        @endif
    </div>
</div>
