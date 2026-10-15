<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header
        eyebrow="Núcleo nacional · Relatórios"
        title="Relatórios Nacionais"
        description="Prepare relatórios institucionais por categoria, província e período, e exporte-os em CSV."
    >
        <x-slot:actions>
            <button type="button" wire:click="exportar" wire:loading.attr="disabled" class="btn-primary">Exportar CSV</button>
            <button type="button" onclick="window.print()" class="btn-ghost border border-zinc-200 dark:border-zinc-700">Imprimir</button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('info'))
        <div class="border-s-4 border-angola-red bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">{{ session('info') }}</div>
    @endif

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="rel-categoria" class="mb-1 block">Categoria</label>
                <select id="rel-categoria" wire:model.live="categoria" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    @foreach($categorias as $chave => $rotulo)<option value="{{ $chave }}">{{ $rotulo }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="rel-provincia" class="mb-1 block">Província</label>
                <select id="rel-provincia" wire:model.live="provinciaId" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todas</option>
                    @foreach($provincias as $id => $nome)<option value="{{ $id }}">{{ $nome }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="rel-de" class="mb-1 block">De</label>
                <input id="rel-de" type="date" wire:model.live="de" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                @error('de')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="rel-ate" class="mb-1 block">Até</label>
                <input id="rel-ate" type="date" wire:model.live="ate" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                @error('ate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <x-ui.card :title="'Relatório — '.$categorias[$categoria]" :description="'Período: '.($de ? \Carbon\Carbon::parse($de)->format('d/m/Y') : 'início').' a '.($ate ? \Carbon\Carbon::parse($ate)->format('d/m/Y') : 'hoje').' · Emitido em '.now()->format('d/m/Y H:i')" padding="p-0">
        <div class="overflow-x-auto">
            <table class="table-gov min-w-full">
                <thead>
                    <tr>
                        <th>Província</th>
                        @foreach($dados['colunas'] as $coluna)<th class="text-right">{{ $coluna['rotulo'] }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($dados['linhas'] as $linha)
                        <tr>
                            <td class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $linha['provincia'] }}</td>
                            @foreach($dados['colunas'] as $coluna)
                                <td class="text-right tabular-nums">{{ $servico->formatar($linha[$coluna['chave']], $coluna['formato']) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($dados['colunas']) + 1 }}" class="!p-0"><x-ui.empty-state title="Sem dados para o filtro" description="Ajuste a categoria, a província ou o período." /></td></tr>
                    @endforelse
                </tbody>
                @if($dados['linhas']->isNotEmpty())
                    <tfoot>
                        <tr class="bg-zinc-50 font-bold dark:bg-zinc-800/60">
                            <td class="px-4 py-3">Total</td>
                            @foreach($dados['colunas'] as $coluna)
                                <td class="px-4 py-3 text-right tabular-nums">{{ $servico->formatar($dados['totais'][$coluna['chave']], $coluna['formato']) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-ui.card>
</div>
