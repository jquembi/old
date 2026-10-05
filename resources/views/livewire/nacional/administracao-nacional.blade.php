<x-slot name="header">
    <h1 class="text-2xl font-bold text-gray-900">Administração Nacional</h1>
    <p class="text-sm text-gray-600">Gestão de tenants e configurações da plataforma</p>
</x-slot>

<div class="space-y-6 px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       wire:model="search" 
                       placeholder="Pesquisar tenant..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex gap-4">
                <select wire:model="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">Todos os Tipos</option>
                    <option value="central">Central</option>
                    <option value="provincial">Provincial</option>
                </select>
                <select wire:model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">Todos os Status</option>
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                </select>
                <button wire:click="$refresh" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Tenants -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Tenants</h2>
            <button wire:click="$set('showModal', true)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Novo Tenant
            </button>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sync</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Online</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tenants as $tenant)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tenant->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tenant->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tenant->type === 'central' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($tenant->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($tenant->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tenant->sync_status === 'synced' ? 'bg-green-100 text-green-800' : ($tenant->sync_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($tenant->sync_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($tenant->is_online)
                                    <span class="text-green-600">● Online</span>
                                @else
                                    <span class="text-gray-400">● Offline</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button wire:click="editTenant({{ $tenant->id }})" class="text-blue-600 hover:text-blue-900">
                                        Editar
                                    </button>
                                    <button wire:click="toggleTenantStatus({{ $tenant->id }})" class="text-yellow-600 hover:text-yellow-900">
                                        {{ $tenant->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    @if(!$tenant->isCentral())
                                        <button wire:click="deleteTenant({{ $tenant->id }})" class="text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tenants->links() }}
        </div>
    </div>

<!-- Modal de Criação/Edição -->
<x-modal wire:model="showModal">
    <div class="bg-white rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            {{ $editingTenant ? 'Editar Tenant' : 'Novo Tenant' }}
        </h2>
        
        <form wire:submit="{{ $editingTenant ? 'updateTenant' : 'createTenant' }}" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" wire:model="tenantData.name" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @error('tenantData.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                <input type="text" wire:model="tenantData.code" class="w-full px-4 py-2 border border-gray-300 rounded-lg" maxlength="10">
                @error('tenantData.code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model="tenantData.type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="central">Central</option>
                    <option value="provincial">Provincial</option>
                </select>
                @error('tenantData.type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="tenantData.is_active" class="mr-2">
                    <span class="text-sm text-gray-700">Ativo</span>
                </label>
            </div>
            
            @if(!$editingTenant)
            <div class="border-t pt-4 mt-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Configuração de Base de Dados</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                        <input type="text" wire:model="tenantData.database_host" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @error('tenantData.database_host') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Porta</label>
                        <input type="number" wire:model="tenantData.database_port" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @error('tenantData.database_port') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da BD</label>
                        <input type="text" wire:model="tenantData.database_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @error('tenantData.database_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Utilizador</label>
                        <input type="text" wire:model="tenantData.database_username" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @error('tenantData.database_username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" wire:model="tenantData.database_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @error('tenantData.database_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ $editingTenant ? 'Atualizar' : 'Criar' }}
                </button>
            </div>
        </form>
    </div>
</x-modal>
</div>
