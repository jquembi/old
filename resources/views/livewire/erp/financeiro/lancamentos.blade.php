<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Lançamentos</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Novo Lançamento
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Tipos</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($lancamentos as $lancamento)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $lancamento->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lancamento->data->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lancamento->descricao }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $lancamento->tipo === 'receita' ? 'bg-blue-100 text-blue-800' : ($lancamento->tipo === 'despesa' ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800') }}">
                                {{ ucfirst($lancamento->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($lancamento->valor, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $lancamento->status === 'confirmado' ? 'bg-blue-100 text-blue-800' : ($lancamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($lancamento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $lancamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($lancamento->isPendente())
                                <button wire:click="aprovar({{ $lancamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Aprovar</button>
                            @endif
                            <button wire:click="delete({{ $lancamento->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $lancamentos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $lancamentoId ? 'Editar Lançamento' : 'Novo Lançamento' }}</h3>
                <form wire:submit="save">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Número</label>
                        <input type="text" wire:model="numero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Data</label>
                        <input type="date" wire:model="data" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('data') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                        <textarea wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tipo</label>
                        <select wire:model="tipo_lancamento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                        @error('tipo_lancamento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Valor (Kz)</label>
                        <input type="number" wire:model="valor" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('valor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Conta de Débito</label>
                        <select wire:model="conta_debito_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione</option>
                            @foreach ($contas as $conta)
                                <option value="{{ $conta->id }}">{{ $conta->codigo }} - {{ $conta->nome }}</option>
                            @endforeach
                        </select>
                        @error('conta_debito_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Conta de Crédito</label>
                        <select wire:model="conta_credito_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione</option>
                            @foreach ($contas as $conta)
                                <option value="{{ $conta->id }}">{{ $conta->codigo }} - {{ $conta->nome }}</option>
                            @endforeach
                        </select>
                        @error('conta_credito_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        @this.on('notify', (message, type) => {
            alert(message);
        });
    </script>
</div>
