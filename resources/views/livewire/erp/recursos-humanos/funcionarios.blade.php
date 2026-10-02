<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Novo Funcionário
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" wire:model.live="search" placeholder="Pesquisar..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="departamento_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Departamentos</option>
                    @foreach ($departamentos as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="cargo_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Cargos</option>
                    @foreach ($cargos as $cargo)
                        <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="ferias">Férias</option>
                    <option value="licenca">Licença</option>
                    <option value="demitido">Demitido</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrato</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($funcionarios as $funcionario)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $funcionario->numero_funcionario }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $funcionario->nome_completo }}</div>
                            <div class="text-xs text-gray-400">{{ $funcionario->nome_curto }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $funcionario->departamento->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $funcionario->cargo->nome ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($funcionario->tipo_contrato) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 text-xs rounded-full {{ $funcionario->isAtivo() ? 'bg-blue-100 text-blue-800' : ($funcionario->isDemitido() ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ ucfirst($funcionario->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="edit({{ $funcionario->id }})" class="text-blue-600 hover:text-blue-900 mr-2">Editar</button>
                            @if ($funcionario->isAtivo())
                                <button wire:click="demitir({{ $funcionario->id }})" class="text-red-600 hover:text-red-900 mr-2">Demitir</button>
                            @endif
                            <button wire:click="delete({{ $funcionario->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $funcionarios->links() }}
    </div>

    @if ($modalOpen)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $funcionarioId ? 'Editar Funcionário' : 'Novo Funcionário' }}</h3>
                <form wire:submit="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Número Funcionário</label>
                            <input type="text" wire:model="numero_funcionario" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('numero_funcionario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nome Curto</label>
                            <input type="text" wire:model="nome_curto" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('nome_curto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nome Completo</label>
                            <input type="text" wire:model="nome_completo" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('nome_completo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">BI</label>
                            <input type="text" wire:model="bi" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('bi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Data Nascimento</label>
                            <input type="date" wire:model="data_nascimento" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('data_nascimento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Género</label>
                            <select wire:model="genero" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                            </select>
                            @error('genero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Telefone</label>
                            <input type="text" wire:model="telefone" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('telefone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Endereço</label>
                            <textarea wire:model="endereco" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Departamento</label>
                            <select wire:model="departamento_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($departamentos as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Cargo</label>
                            <select wire:model="cargo_id_form" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Data Admissão</label>
                            <input type="date" wire:model="data_admissao" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('data_admissao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Salário Base (Kz)</label>
                            <input type="number" wire:model="salario_base" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('salario_base') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Contrato</label>
                            <select wire:model="tipo_contrato" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="efetivo">Efetivo</option>
                                <option value="contrato">Contrato</option>
                                <option value="estagio">Estágio</option>
                                <option value="temporario">Temporário</option>
                            </select>
                            @error('tipo_contrato') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
