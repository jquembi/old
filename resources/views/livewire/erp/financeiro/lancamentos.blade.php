<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Lançamentos" description="Consultar e gerir os movimentos contabilísticos.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Novo Lançamento
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por tipo" wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Tipos</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por status" wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($lancamentos as $lancamento)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $lancamento->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $lancamento->data->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $lancamento->descricao }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $lancamento->tipo === 'receita' ? 'bg-blue-100 text-blue-800' : ($lancamento->tipo === 'despesa' ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800') }}">
                                {{ ucfirst($lancamento->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($lancamento->valor, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $lancamento->status === 'confirmado' ? 'bg-blue-100 text-blue-800' : ($lancamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($lancamento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $lancamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($lancamento->isPendente())
                                <button wire:click="aprovar({{ $lancamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Aprovar</button>
                            @endif
                            <button wire:click="delete({{ $lancamento->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $lancamentos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-lg p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $lancamentoId ? 'Editar Lançamento' : 'Novo Lançamento' }}</h3>
                <form wire:submit="save">
                    <div class="mb-4">
                        <label for="erp-numero" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Número</label>
                        <input id="erp-numero" type="text" wire:model="numero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                        @error('numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-data" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data</label>
                        <input id="erp-data" type="date" wire:model="data" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                        @error('data') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-descricao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Descrição</label>
                        <textarea id="erp-descricao" wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                        @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-tipo_lancamento" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Tipo</label>
                        <select id="erp-tipo_lancamento" wire:model="tipo_lancamento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                        @error('tipo_lancamento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-valor" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Valor (Kz)</label>
                        <input id="erp-valor" type="number" wire:model="valor" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                        @error('valor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-conta_debito_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Conta de Débito</label>
                        <select id="erp-conta_debito_id" wire:model="conta_debito_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="">Selecione</option>
                            @foreach ($contas as $conta)
                                <option value="{{ $conta->id }}">{{ $conta->codigo }} - {{ $conta->nome }}</option>
                            @endforeach
                        </select>
                        @error('conta_debito_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-conta_credito_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Conta de Crédito</label>
                        <select id="erp-conta_credito_id" wire:model="conta_credito_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="">Selecione</option>
                            @foreach ($contas as $conta)
                                <option value="{{ $conta->id }}">{{ $conta->codigo }} - {{ $conta->nome }}</option>
                            @endforeach
                        </select>
                        @error('conta_credito_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('modalOpen', false)" class="btn-ghost">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
