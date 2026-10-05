<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Contas Gerais" description="Organizar o plano de contas e a sua hierarquia.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Nova Conta
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por tipo" wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Tipos</option>
                    <option value="ativo">Ativo</option>
                    <option value="passivo">Passivo</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por nivel" wire:model.live="nivel" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
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

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nível</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($contas as $conta)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $conta->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $conta->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $conta->tipo === 'ativo' ? 'bg-blue-100 text-blue-800' : ($conta->tipo === 'passivo' ? 'bg-red-100 text-red-800' : ($conta->tipo === 'receita' ? 'bg-indigo-100 text-indigo-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ ucfirst($conta->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $conta->nivel }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $conta->activo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $conta->activo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $conta->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                            <button wire:click="toggleActive({{ $conta->id }})" class="text-orange-600 hover:text-orange-900 mr-3">{{ $conta->activo ? 'Desativar' : 'Ativar' }}</button>
                            <button wire:click="delete({{ $conta->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $contas->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-lg p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $contaId ? 'Editar Conta' : 'Nova Conta' }}</h3>
                <form wire:submit="save">
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
                    <div class="mb-4">
                        <label for="erp-descricao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Descrição</label>
                        <textarea id="erp-descricao" wire:model="descricao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="erp-tipo_conta" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Tipo</label>
                        <select id="erp-tipo_conta" wire:model="tipo_conta" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="ativo">Ativo</option>
                            <option value="passivo">Passivo</option>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                        </select>
                        @error('tipo_conta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-nivel_conta" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Nível</label>
                        <input id="erp-nivel_conta" type="number" wire:model="nivel_conta" min="1" max="5" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                        @error('nivel_conta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-conta_pai_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Conta Pai</label>
                        <select id="erp-conta_pai_id" wire:model="conta_pai_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="">Nenhuma</option>
                            @foreach ($contasPai as $cp)
                                <option value="{{ $cp->id }}">{{ $cp->codigo }} - {{ $cp->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="activo" class="mr-2">
                            <span class="text-zinc-700 dark:text-zinc-300 text-sm">Ativo</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('modalOpen', false)" class="btn-ghost">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
