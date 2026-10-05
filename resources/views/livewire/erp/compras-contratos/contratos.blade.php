<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Contratos" description="Gerir os contratos e acompanhar a sua execução.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Novo Contrato
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por status" wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Status</option>
                    <option value="rascunho">Rascunho</option>
                    <option value="pendente_assinatura">Pendente Assinatura</option>
                    <option value="ativo">Ativo</option>
                    <option value="suspenso">Suspenso</option>
                    <option value="concluido">Concluído</option>
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por tipo" wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Tipos</option>
                    <option value="compra_bens">Compra de Bens</option>
                    <option value="prestacao_servicos">Prestação de Serviços</option>
                    <option value="obra">Obra</option>
                    <option value="consultoria">Consultoria</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Fornecedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Início</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Fim</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($contratos as $contrato)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $contrato->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $contrato->fornecedor->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $contrato->getTipoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $contrato->data_inicio->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div>{{ $contrato->data_fim->format('d/m/Y') }}</div>
                            @if ($contrato->isVencido())
                                <span class="text-xs text-red-600">Vencido</span>
                            @elseif ($contrato->isAVencer())
                                <span class="text-xs text-yellow-600">A vencer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($contrato->valor_total, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $contrato->isAtivo() ? 'bg-blue-100 text-blue-800' : ($contrato->isSuspenso() ? 'bg-orange-100 text-orange-800' : ($contrato->isConcluido() ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $contrato->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
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
                @empty
                    <tr><td colspan="8" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $contratos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-2xl p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $contratoId ? 'Editar Contrato' : 'Novo Contrato' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="erp-numero" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Número</label>
                        <input id="erp-numero" type="text" wire:model="numero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-tipo_contrato" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Tipo</label>
                        <select id="erp-tipo_contrato" wire:model="tipo_contrato" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="compra_bens">Compra de Bens</option>
                                <option value="prestacao_servicos">Prestação de Serviços</option>
                                <option value="obra">Obra</option>
                                <option value="consultoria">Consultoria</option>
                            </select>
                            @error('tipo_contrato') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-data_inicio" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data Início</label>
                        <input id="erp-data_inicio" type="date" wire:model="data_inicio" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('data_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-data_fim" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data Fim</label>
                        <input id="erp-data_fim" type="date" wire:model="data_fim" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('data_fim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-descricao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Descrição</label>
                        <textarea id="erp-descricao" wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-fornecedor_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Fornecedor</label>
                        <select id="erp-fornecedor_id" wire:model="fornecedor_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                                @endforeach
                            </select>
                            @error('fornecedor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-requisicao_compra_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Requisição de Compra</label>
                        <select id="erp-requisicao_compra_id" wire:model="requisicao_compra_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($requisicoes as $requisicao)
                                    <option value="{{ $requisicao->id }}">{{ $requisicao->numero }} - {{ Str::limit($requisicao->descricao, 30) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-valor_total" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Valor Total (Kz)</label>
                        <input id="erp-valor_total" type="number" wire:model="valor_total" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('valor_total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-condicoes" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Condições</label>
                        <textarea id="erp-condicoes" wire:model="condicoes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-arquivo_path" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Arquivo</label>
                        <input id="erp-arquivo_path" type="text" wire:model="arquivo_path" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red" placeholder="Caminho do arquivo">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" wire:click="$set('modalOpen', false)" class="btn-ghost">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
