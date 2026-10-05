<div class="ui-module px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">
    <x-ui.page-header eyebrow="Gestão e ERP" title="Funcionários" description="Gerir os funcionários e os seus dados profissionais.">
        <x-slot:actions><button wire:click="create" class="btn-primary">
            Novo Funcionário
        </button></x-slot:actions>
    </x-ui.page-header>

    <div class="ui-card p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <div>
                <select aria-label="Filtrar por cargo id" wire:model.live="cargo_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Cargos</option>
                    @foreach ($cargos as $cargo)
                        <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select aria-label="Filtrar por status" wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                    <option value="">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="ferias">Férias</option>
                    <option value="licenca">Licença</option>
                    <option value="demitido">Demitido</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ui-card overflow-x-auto">
        <table class="table-gov min-w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nº</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cargo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Contrato</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($funcionarios as $funcionario)
<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-50">{{ $funcionario->numero_funcionario }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div>{{ $funcionario->nome_completo }}</div>
                            <div class="text-xs text-gray-400">{{ $funcionario->nome_curto }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $funcionario->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $funcionario->cargo->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ ucfirst($funcionario->tipo_contrato) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="px-2 py-1 text-xs rounded-full {{ $funcionario->isAtivo() ? 'bg-blue-100 text-blue-800' : ($funcionario->isDemitido() ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ ucfirst($funcionario->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <button wire:click="edit({{ $funcionario->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($funcionario->isAtivo())
                                <button wire:click="demitir({{ $funcionario->id }})" class="text-red-600 hover:text-red-900 mr-2">Demitir</button>
                            @endif
                            <button wire:click="delete({{ $funcionario->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center"><x-ui.empty-state title="Nenhum registo encontrado" description="Ajuste os filtros ou adicione um novo registo." /></td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $funcionarios->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-950/60 p-4 backdrop-blur-sm">
            <div role="dialog" aria-modal="true" aria-label="Formulário de registo" class="ui-card relative mx-auto my-8 w-full max-w-2xl p-6">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-50 mb-4">{{ $funcionarioId ? 'Editar Funcionário' : 'Novo Funcionário' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="erp-numero_funcionario" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Número Funcionário</label>
                        <input id="erp-numero_funcionario" type="text" wire:model="numero_funcionario" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('numero_funcionario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-nome_curto" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Nome Curto</label>
                        <input id="erp-nome_curto" type="text" wire:model="nome_curto" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('nome_curto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-nome_completo" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Nome Completo</label>
                        <input id="erp-nome_completo" type="text" wire:model="nome_completo" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('nome_completo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-bi" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">BI</label>
                        <input id="erp-bi" type="text" wire:model="bi" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('bi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-data_nascimento" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data Nascimento</label>
                        <input id="erp-data_nascimento" type="date" wire:model="data_nascimento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('data_nascimento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-genero" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Género</label>
                        <select id="erp-genero" wire:model="genero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                            </select>
                            @error('genero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-telefone" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Telefone</label>
                        <input id="erp-telefone" type="text" wire:model="telefone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('telefone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-email" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Email</label>
                        <input id="erp-email" type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label for="erp-endereco" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Endereço</label>
                        <textarea id="erp-endereco" wire:model="endereco" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red"></textarea>
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
                        <div class="mb-4">
                            <label for="erp-cargo_id_form" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Cargo</label>
                        <select id="erp-cargo_id_form" wire:model="cargo_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="">Selecione</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="erp-data_admissao" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Data Admissão</label>
                        <input id="erp-data_admissao" type="date" wire:model="data_admissao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('data_admissao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-salario_base" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Salário Base (Kz)</label>
                        <input id="erp-salario_base" type="number" wire:model="salario_base" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                            @error('salario_base') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="erp-tipo_contrato" class="block text-zinc-700 dark:text-zinc-300 text-sm font-bold mb-2">Tipo de Contrato</label>
                        <select id="erp-tipo_contrato" wire:model="tipo_contrato" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-angola-red">
                                <option value="efetivo">Efetivo</option>
                                <option value="contrato">Contrato</option>
                                <option value="estagio">Estágio</option>
                                <option value="temporario">Temporário</option>
                            </select>
                            @error('tipo_contrato') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
