<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Património" description="Consultar e gerir o inventário de bens.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Novo Património
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por categoria id" wire:model.live="categoria_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todas as Categorias</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por localizacao id" wire:model.live="localizacao_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todas as Localizações</option>
                    @foreach ($localizacoes as $localizacao)
                        <option value="{{ $localizacao->id }}">{{ $localizacao->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por estado" wire:model.live="estado" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
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
                <select aria-label="Filtrar por status" wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                    <option value="manutencao">Em Manutenção</option>
                    <option value="baixa">Baixa</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Localização</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Valor Atual</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($patrimonios as $patrimonio)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $patrimonio->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div>{{ $patrimonio->nome }}</div>
                            @if ($patrimonio->numero_serie)
                                <div class="text-xs text-gray-400">S/N: {{ $patrimonio->numero_serie }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $patrimonio->categoriaPatrimonio->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $patrimonio->localizacao->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $patrimonio->getEstadoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $patrimonio->valor_atual ? number_format($patrimonio->valor_atual, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $patrimonio->isAtivo() ? 'bg-blue-100 text-blue-800' : ($patrimonio->isManutencao() ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $patrimonio->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $patrimonio->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            <button wire:click="delete({{ $patrimonio->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $patrimonios->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-2xl p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $patrimonioId ? 'Editar Património' : 'Novo Património' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="erp-codigo" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Código</label>
                        <input id="erp-codigo" type="text" wire:model="codigo" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-nome" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Nome</label>
                        <input id="erp-nome" type="text" wire:model="nome" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('nome') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-descricao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Descrição</label>
                        <textarea id="erp-descricao" wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="erp-categoria_patrimonio_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Categoria</label>
                        <select id="erp-categoria_patrimonio_id" wire:model="categoria_patrimonio_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-localizacao_id_form" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Localização</label>
                        <select id="erp-localizacao_id_form" wire:model="localizacao_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($localizacoes as $localizacao)
                                    <option value="{{ $localizacao->id }}">{{ $localizacao->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-data_aquisicao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data Aquisição</label>
                        <input id="erp-data_aquisicao" type="date" wire:model="data_aquisicao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('data_aquisicao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-valor_aquisicao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Valor Aquisição (Kz)</label>
                        <input id="erp-valor_aquisicao" type="number" wire:model="valor_aquisicao" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('valor_aquisicao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-valor_atual" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Valor Atual (Kz)</label>
                        <input id="erp-valor_atual" type="number" wire:model="valor_atual" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('valor_atual') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-estado_patrimonio" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Estado</label>
                        <select id="erp-estado_patrimonio" wire:model="estado_patrimonio" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
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
                            <label for="erp-numero_serie" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Número de Série</label>
                        <input id="erp-numero_serie" type="text" wire:model="numero_serie" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                        </div>
                        <div class="mb-4">
                            <label for="erp-fornecedor" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Fornecedor</label>
                        <input id="erp-fornecedor" type="text" wire:model="fornecedor" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
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
