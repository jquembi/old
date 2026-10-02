<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Requisições de Compra</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Nova Requisição
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
                    <option value="rascunho">Rascunho</option>
                    <option value="pendente_aprovacao">Pendente Aprovação</option>
                    <option value="aprovado">Aprovado</option>
                    <option value="rejeitado">Rejeitado</option>
                    <option value="em_processamento">Em Processamento</option>
                    <option value="concluido">Concluído</option>
                </select>
            </div>
            <div>
                <select wire:model.live="prioridade" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as Prioridades</option>
                    <option value="baixa">Baixa</option>
                    <option value="normal">Normal</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Estimado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($requisicoes as $requisicao)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $requisicao->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $requisicao->data->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($requisicao->descricao, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $requisicao->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $requisicao->getPrioridadeBadgeClass() === 'danger' ? 'bg-red-100 text-red-800' : ($requisicao->getPrioridadeBadgeClass() === 'warning' ? 'bg-yellow-100 text-yellow-800' : ($requisicao->getPrioridadeBadgeClass() === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($requisicao->prioridade) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $requisicao->valor_estimado ? number_format($requisicao->valor_estimado, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $requisicao->isAprovado() ? 'bg-blue-100 text-blue-800' : ($requisicao->isPendenteAprovacao() ? 'bg-orange-100 text-orange-800' : ($requisicao->isRejeitado() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $requisicao->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $requisicao->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($requisicao->isRascunho())
                                <button wire:click="submeterParaAprovacao({{ $requisicao->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Submeter</button>
                            @endif
                            @if ($requisicao->isPendenteAprovacao())
                                <button wire:click="aprovar({{ $requisicao->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Aprovar</button>
                                <button wire:click="rejeitar({{ $requisicao->id }})" class="text-red-600 hover:text-red-900 mr-2">Rejeitar</button>
                            @endif
                            <button wire:click="delete({{ $requisicao->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $requisicoes->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $requisicaoId ? 'Editar Requisição' : 'Nova Requisição' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
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
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                            <textarea wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Departamento</label>
                            <select wire:model="departamento_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($departamentos as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Solicitante</label>
                            <select wire:model="solicitante_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($solicitantes as $solicitante)
                                    <option value="{{ $solicitante->id }}">{{ $solicitante->nome_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Prioridade</label>
                            <select wire:model="prioridade_req" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="baixa">Baixa</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                            @error('prioridade_req') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Valor Estimado (Kz)</label>
                            <input type="number" wire:model="valor_estimado" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('valor_estimado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
