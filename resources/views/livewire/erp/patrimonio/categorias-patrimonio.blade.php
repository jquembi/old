<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Categorias de Património</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Nova Categoria
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div>
            <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria Pai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patrimónios</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($categorias as $categoria)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $categoria->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $categoria->nome }}</div>
                            @if ($categoria->descricao)
                                <div class="text-xs text-gray-400">{{ Str::limit($categoria->descricao, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $categoria->categoriaPai->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $categoria->numero_patrimonios }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($categoria->valor_total_patrimonios, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $categoria->activo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $categoria->activo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $categoria->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            <button wire:click="toggleActive({{ $categoria->id }})" class="text-orange-600 hover:text-orange-900 mr-2">{{ $categoria->activo ? 'Desativar' : 'Ativar' }}</button>
                            <button wire:click="delete({{ $categoria->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $categorias->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $categoriaId ? 'Editar Categoria' : 'Nova Categoria' }}</h3>
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
                        <label class="block text-gray-700 text-sm font-bold mb-2">Categoria Pai</label>
                        <select wire:model="categoria_pai_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Nenhuma (Raiz)</option>
                            @foreach ($categoriasPai as $cp)
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
