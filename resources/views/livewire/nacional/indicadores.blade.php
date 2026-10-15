<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header
        eyebrow="Núcleo nacional · Indicadores"
        :title="'Indicadores — '.$categorias[$categoria]"
        description="Dados consolidados em tempo real a partir de todas as províncias activas."
    >
        <x-slot:actions>
            <a href="{{ route('nacional.relatorios', ['categoria' => $categoria]) }}" wire:navigate class="btn-primary">Gerar relatório</a>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="flex flex-wrap gap-2" aria-label="Categorias de indicadores">
        @foreach($categorias as $chave => $rotulo)
            @php
                $rota = $chave === 'geral' ? 'nacional.indicadores' : 'nacional.indicadores.'.$chave;
            @endphp
            <a href="{{ route($rota) }}" wire:navigate
               class="{{ $categoria === $chave ? 'btn-primary' : 'btn-ghost border border-zinc-200 dark:border-zinc-700' }}">{{ $rotulo }}</a>
        @endforeach
    </nav>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <select aria-label="Filtrar por província" wire:model.live="provinciaId" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <option value="">Todas as províncias</option>
                @foreach($provincias as $id => $nome)
                    <option value="{{ $id }}">{{ $nome }}</option>
                @endforeach
            </select>
            <select aria-label="Período da evolução" wire:model.live="meses" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                <option value="3">Últimos 3 meses</option>
                <option value="6">Últimos 6 meses</option>
                <option value="12">Últimos 12 meses</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(array_slice($dados['colunas'], 0, 4) as $i => $coluna)
            <x-ui.stat-card
                :label="$coluna['rotulo']"
                :value="$servico->formatar($dados['totais'][$coluna['chave']], $coluna['formato'])"
                :detail="$provinciaId ? ($provincias[$provinciaId] ?? 'Província') : $geral['provincias'].' províncias · '.$geral['municipios'].' municípios'"
                :tone="['red', 'green', 'violet', 'yellow'][$i % 4]"
            />
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-ui.card title="Evolução mensal" description="Registos criados em cada mês.">
            @php $max = max(1, max(array_column($evolucao, 'valor'))); @endphp
            <div class="flex h-48 items-end gap-2" role="img" aria-label="Gráfico de evolução mensal">
                @foreach($evolucao as $ponto)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $ponto['valor'] }}</span>
                        <div class="w-full rounded-t bg-angola-red" style="height: {{ max(2, round($ponto['valor'] / $max * 100)) }}%"></div>
                        <span class="text-xs text-zinc-500">{{ $ponto['mes'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card title="Distribuição por estado" description="Situação actual dos registos principais desta categoria.">
            @php $totalEstados = max(1, array_sum($estados)); @endphp
            <div class="space-y-3">
                @forelse($estados as $estado => $total)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="capitalize text-zinc-700 dark:text-zinc-300">{{ str_replace('_', ' ', $estado ?: 'sem estado') }}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $total }} · {{ round($total / $totalEstados * 100) }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-2 rounded-full bg-angola-red" style="width: {{ round($total / $totalEstados * 100) }}%"></div></div>
                    </div>
                @empty
                    <x-ui.empty-state title="Sem registos" description="Ainda não existem dados nesta categoria." />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <div class="ui-card overflow-x-auto">
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
                    <tr><td colspan="{{ count($dados['colunas']) + 1 }}" class="!p-0"><x-ui.empty-state title="Nenhuma província encontrada" description="Registe províncias activas para ver a comparação." /></td></tr>
                @endforelse
            </tbody>
            @if($dados['linhas']->count() > 1)
                <tfoot>
                    <tr class="bg-zinc-50 font-bold dark:bg-zinc-800/60">
                        <td class="px-4 py-3">Total nacional</td>
                        @foreach($dados['colunas'] as $coluna)
                            <td class="px-4 py-3 text-right tabular-nums">{{ $servico->formatar($dados['totais'][$coluna['chave']], $coluna['formato']) }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
