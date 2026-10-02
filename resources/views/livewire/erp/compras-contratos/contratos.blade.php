<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Contratos</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Novo Contrato
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
                    <option value="pendente_assinatura">Pendente Assinatura</option>
                    <option value="ativo">Ativo</option>
                    <option value="suspenso">Suspenso</option>
                    <option value="concluido">Concluído</option>
                </select>
            </div>
            <div>
                <select wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Tipos</option>
                    <option value="compra_bens">Compra de Bens</option>
                    <option value="prestacao_servicos">Prestação de Serviços</option>
                    <option value="obra">Obra</option>
                    <option value="consultoria">Consultoria</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Início</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fim</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($contratos as $contrato)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $contrato->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contrato->fornecedor->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contrato->getTipoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contrato->data_inicio->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $contrato->data_fim->format('d/m/Y') }}</div>
                            @if ($contrato->isVencido())
                                <span class="text-xs text-red-600">Vencido</span>
                            @elseif ($contrato->isAVencer())
                                <span class="text-xs text-yellow-600">A vencer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($contrato->valor_total, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $contrato->isAtivo() ? 'bg-blue-100 text-blue-800' : ($contrato->isSuspenso() ? 'bg-orange-100 text-orange-800' : ($contrato->isConcluido() ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $contrato->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $contrato->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($contrato->isRascunho() || $contrato->isPendenteAssinatura())
                                <button wire:click="ativar({{ $contrato->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Ativar</button>
                            @endif
                            @if ($contrato->isAtivo())
                                <button wire:click="suspender({{ $contrato->id }})" class="text-orange-600 hover:text-orange-900 mr-2">Suspender</button>
                                <button wire:click="concluir({{ $contrato->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Concluir</button>
                            @endif
                            <button wire:click="delete({{ $contrato->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $contratos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $contratoId ? 'Editar Contrato' : 'Novo Contrato' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Número</label>
                            <input type="text" wire:model="numero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo</label>
                            <select wire:model="tipo_contrato" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="compra_bens">Compra de Bens</option>
                                <option value="prestacao_servicos">Prestação de Serviços</option>
                                <option value="obra">Obra</option>
                                <option value="consultoria">Consultoria</option>
                            </select>
                            @error('tipo_contrato') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Data Início</label>
                            <input type="date" wire:model="data_inicio" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('data_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Data Fim</label>
                            <input type="date" wire:model="data_fim" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('data_fim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Descrição</label>
                            <textarea wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fornecedor</label>
                            <select wire:model="fornecedor_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                                @endforeach
                            </select>
                            @error('fornecedor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Requisição de Compra</label>
                            <select wire:model="requisicao_compra_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($requisicoes as $requisicao)
                                    <option value="{{ $requisicao->id }}">{{ $requisicao->numero }} - {{ Str::limit($requisicao->descricao, 30) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Valor Total (Kz)</label>
                            <input type="number" wire:model="valor_total" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('valor_total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Condições</label>
                            <textarea wire:model="condicoes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Arquivo</label>
                            <input type="text" wire:model="arquivo_path" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Caminho do arquivo">
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
