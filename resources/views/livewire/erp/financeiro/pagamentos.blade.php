<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pagamentos</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Novo Pagamento
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <select wire:model.live="metodo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Métodos</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                    <option value="multicaixa">Multicaixa</option>
                    <option value="cheque">Cheque</option>
                    <option value="cartao">Cartão</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referência</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($pagamentos as $pagamento)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pagamento->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pagamento->data_pagamento->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($pagamento->valor, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pagamento->getMetodoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pagamento->referencia ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $pagamento->status === 'confirmado' ? 'bg-blue-100 text-blue-800' : ($pagamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($pagamento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $pagamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($pagamento->isPendente())
                                <button wire:click="confirmar({{ $pagamento->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Confirmar</button>
                            @endif
                            <button wire:click="delete({{ $pagamento->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $pagamentos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $pagamentoId ? 'Editar Pagamento' : 'Novo Pagamento' }}</h3>
                <form wire:submit="save">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Número</label>
                        <input type="text" wire:model="numero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Data</label>
                        <input type="date" wire:model="data_pagamento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('data_pagamento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Valor (Kz)</label>
                        <input type="number" wire:model="valor" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('valor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Método de Pagamento</label>
                        <select wire:model="metodo_pagamento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="transferencia">Transferência</option>
                            <option value="multicaixa">Multicaixa</option>
                            <option value="cheque">Cheque</option>
                            <option value="cartao">Cartão</option>
                        </select>
                        @error('metodo_pagamento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Referência</label>
                        <input type="text" wire:model="referencia" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Observações</label>
                        <textarea wire:model="observacoes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
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
