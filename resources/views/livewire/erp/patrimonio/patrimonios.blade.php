<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Património</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Novo Património
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="categoria_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as Categorias</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="localizacao_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as Localizações</option>
                    @foreach ($localizacoes as $localizacao)
                        <option value="{{ $localizacao->id }}">{{ $localizacao->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="estado" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Estados</option>
                    <option value="novo">Novo</option>
                    <option value="bom">Bom</option>
                    <option value="razoavel">Razoável</option>
                    <option value="mau">Mau</option>
                    <option value="danificado">Danificado</option>
                    <option value="obsoleto">Obsoleto</option>
                </select>
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="manutencao">Em Manutenção</option>
                    <option value="baixa">Baixa</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Atual</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($patrimonios as $patrimonio)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $patrimonio->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $patrimonio->nome }}</div>
                            @if ($patrimonio->numero_serie)
                                <div class="text-xs text-gray-400">S/N: {{ $patrimonio->numero_serie }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patrimonio->categoriaPatrimonio->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patrimonio->localizacao->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patrimonio->getEstadoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patrimonio->valor_atual ? number_format($patrimonio->valor_atual, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $patrimonio->isAtivo() ? 'bg-blue-100 text-blue-800' : ($patrimonio->isManutencao() ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $patrimonio->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $patrimonio->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            <button wire:click="delete({{ $patrimonio->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $patrimonios->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $patrimonioId ? 'Editar Património' : 'Novo Património' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
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
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                            <textarea wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Categoria</label>
                            <select wire:model="categoria_patrimonio_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Localização</label>
                            <select wire:model="localizacao_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($localizacoes as $localizacao)
                                    <option value="{{ $localizacao->id }}">{{ $localizacao->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Data Aquisição</label>
                            <input type="date" wire:model="data_aquisicao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('data_aquisicao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Valor Aquisição (Kz)</label>
                            <input type="number" wire:model="valor_aquisicao" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('valor_aquisicao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Valor Atual (Kz)</label>
                            <input type="number" wire:model="valor_atual" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('valor_atual') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                            <select wire:model="estado_patrimonio" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="novo">Novo</option>
                                <option value="bom">Bom</option>
                                <option value="razoavel">Razoável</option>
                                <option value="mau">Mau</option>
                                <option value="danificado">Danificado</option>
                                <option value="obsoleto">Obsoleto</option>
                            </select>
                            @error('estado_patrimonio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Número de Série</label>
                            <input type="text" wire:model="numero_serie" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fornecedor</label>
                            <input type="text" wire:model="fornecedor" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Observações</label>
                            <textarea wire:model="observacoes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
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
