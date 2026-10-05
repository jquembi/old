<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Cargos" description="Gerir os cargos e as referências salariais.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Novo Cargo
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <input type="text" aria-label="Pesquisar registos" wire:model.live.debounce.300ms="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
            </div>
            <div>
                <select aria-label="Filtrar por departamento id" wire:model.live="departamento_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Departamentos</option>
                    @foreach ($departamentos as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                    @endforeach
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Salário Mínimo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Salário Máximo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Funcionários</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($cargos as $cargo)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $cargo->codigo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div>{{ $cargo->nome }}</div>
                            @if ($cargo->descricao)
                                <div class="text-xs text-gray-400">{{ Str::limit($cargo->descricao, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $cargo->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $cargo->salario_minimo ? number_format($cargo->salario_minimo, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $cargo->salario_maximo ? number_format($cargo->salario_maximo, 2, ',', '.') . ' Kz' : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $cargo->numero_funcionarios }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $cargo->activo ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $cargo->activo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $cargo->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            <button wire:click="toggleActive({{ $cargo->id }})" class="text-orange-600 hover:text-orange-900 mr-2">{{ $cargo->activo ? 'Desativar' : 'Ativar' }}</button>
                            <button wire:click="delete({{ $cargo->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $cargos->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-lg p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $cargoId ? 'Editar Cargo' : 'Novo Cargo' }}</h3>
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
                        <label for="erp-departamento_id_form" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Departamento</label>
                        <select id="erp-departamento_id_form" wire:model="departamento_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            <option value="">Selecione</option>
                            @foreach ($departamentos as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="erp-salario_minimo" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Salário Mínimo (Kz)</label>
                        <input id="erp-salario_minimo" type="number" wire:model="salario_minimo" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('salario_minimo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="erp-salario_maximo" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Salário Máximo (Kz)</label>
                        <input id="erp-salario_maximo" type="number" wire:model="salario_maximo" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('salario_maximo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
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
