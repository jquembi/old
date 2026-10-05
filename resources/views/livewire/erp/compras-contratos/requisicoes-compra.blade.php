<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Requisições de Compra" description="Acompanhar as requisições e os pedidos de compra.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Nova Requisição
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
                    <option value="pendente_aprovacao">Pendente Aprovação</option>
                    <option value="aprovado">Aprovado</option>
                    <option value="rejeitado">Rejeitado</option>
                    <option value="em_processamento">Em Processamento</option>
                    <option value="concluido">Concluído</option>
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por prioridade" wire:model.live="prioridade" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todas as Prioridades</option>
                    <option value="baixa">Baixa</option>
                    <option value="normal">Normal</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Prioridade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Valor Estimado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($requisicoes as $requisicao)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $requisicao->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $requisicao->data->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($requisicao->descricao, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $requisicao->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $requisicao->getPrioridadeBadgeClass() === 'danger' ? 'bg-red-100 text-red-800' : ($requisicao->getPrioridadeBadgeClass() === 'warning' ? 'bg-yellow-100 text-yellow-800' : ($requisicao->getPrioridadeBadgeClass() === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($requisicao->prioridade) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $requisicao->valor_estimado ? number_format($requisicao->valor_estimado, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $requisicao->isAprovado() ? 'bg-blue-100 text-blue-800' : ($requisicao->isPendenteAprovacao() ? 'bg-orange-100 text-orange-800' : ($requisicao->isRejeitado() ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $requisicao->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
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
                @empty
                    <tr><td colspan="8" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $requisicoes->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-2xl p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $requisicaoId ? 'Editar Requisição' : 'Nova Requisição' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
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
                        <div class="mb-4 col-span-2">
                            <label for="erp-descricao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Descrição</label>
                        <textarea id="erp-descricao" wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                            @error('descricao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-departamento_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Departamento</label>
                        <select id="erp-departamento_id" wire:model="departamento_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($departamentos as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-solicitante_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Solicitante</label>
                        <select id="erp-solicitante_id" wire:model="solicitante_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($solicitantes as $solicitante)
                                    <option value="{{ $solicitante->id }}">{{ $solicitante->nome_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-prioridade_req" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Prioridade</label>
                        <select id="erp-prioridade_req" wire:model="prioridade_req" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="baixa">Baixa</option>
                                <option value="normal">Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                            @error('prioridade_req') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-valor_estimado" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Valor Estimado (Kz)</label>
                        <input id="erp-valor_estimado" type="number" wire:model="valor_estimado" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('valor_estimado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-observacoes" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Observações</label>
                        <textarea id="erp-observacoes" wire:model="observacoes" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
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
