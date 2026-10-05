<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Localizações" description="Gerir as localizações dos bens da instituição.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Nova Localização
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por tipo" wire:model.live="tipo" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Tipos</option>
                    <option value="edificio">Edifício</option>
                    <option value="sala">Sala</option>
                    <option value="armazem">Armazém</option>
                    <option value="garagem">Garagem</option>
                    <option value="outro">Outro</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Patrimónios</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Valor Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($localizacoes as $localizacao)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $localizacao->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div>{{ $localizacao->nome }}</div>
                            @if ($localizacao->descricao)
                                <div class="text-xs text-gray-400">{{ Str::limit($localizacao->descricao, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $localizacao->getTipoLabel() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $localizacao->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $localizacao->numero_patrimonios }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($localizacao->valor_patrimonios, 2, ',', '.') }} Kz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $localizacao->activo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $localizacao->activo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $localizacao->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            <button wire:click="toggleActive({{ $localizacao->id }})" class="text-orange-600 hover:text-orange-900 mr-2">{{ $localizacao->activo ? 'Desativar' : 'Ativar' }}</button>
                            <button wire:click="delete({{ $localizacao->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $localizacoes->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-lg p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $localizacaoId ? 'Editar Localização' : 'Nova Localização' }}</h3>
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
                        <label for="erp-tipo_localizacao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Tipo</label>
                        <select id="erp-tipo_localizacao" wire:model="tipo_localizacao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="edificio">Edifício</option>
                            <option value="sala">Sala</option>
                            <option value="armazem">Armazém</option>
                            <option value="garagem">Garagem</option>
                            <option value="outro">Outro</option>
                        </select>
                        @error('tipo_localizacao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="erp-departamento_id" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Departamento</label>
                        <select id="erp-departamento_id" wire:model="departamento_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="">Selecione</option>
                            @foreach ($departamentos as $departamento)
                                <option value="{{ $departamento->id }}">{{ $departamento->nome }}</option>
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
