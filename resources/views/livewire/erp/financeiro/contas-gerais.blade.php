<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Contas Gerais</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Nova Conta
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Tipos</option>
                    <option value="ativo">Ativo</option>
                    <option value="passivo">Passivo</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>
            <div>
                <select wire:model.live="nivel" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Níveis</option>
                    <option value="1">Nível 1</option>
                    <option value="2">Nível 2</option>
                    <option value="3">Nível 3</option>
                    <option value="4">Nível 4</option>
                    <option value="5">Nível 5</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($contas as $conta)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $conta->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conta->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $conta->tipo === 'ativo' ? 'bg-blue-100 text-blue-800' : ($conta->tipo === 'passivo' ? 'bg-red-100 text-red-800' : ($conta->tipo === 'receita' ? 'bg-indigo-100 text-indigo-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ ucfirst($conta->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conta->nivel }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $conta->activo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $conta->activo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $conta->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                            <button wire:click="toggleActive({{ $conta->id }})" class="text-orange-600 hover:text-orange-900 mr-3">{{ $conta->activo ? 'Desativar' : 'Ativar' }}</button>
                            <button wire:click="delete({{ $conta->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $contas->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $contaId ? 'Editar Conta' : 'Nova Conta' }}</h3>
                <form wire:submit="save">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Código</label>
                        <input type="text" wire:model="codigo" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
                        <input type="text" wire:model="nome" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('nome') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                        <textarea wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tipo</label>
                        <select wire:model="tipo_conta" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="ativo">Ativo</option>
                            <option value="passivo">Passivo</option>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                        </select>
                        @error('tipo_conta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nível</label>
                        <input type="number" wire:model="nivel_conta" min="1" max="5" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('nivel_conta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Conta Pai</label>
                        <select wire:model="conta_pai_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Nenhuma</option>
                            @foreach ($contasPai as $cp)
                                <option value="{{ $cp->id }}">{{ $cp->codigo }} - {{ $cp->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="activo" class="mr-2">
                            <span class="text-gray-700 text-sm">Ativo</span>
                        </label>
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
