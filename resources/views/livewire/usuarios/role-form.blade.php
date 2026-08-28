<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" wire:navigate
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $role->getLabel() }}</h1>
                <p class="text-sm text-gray-500">Gerenciar permissoes do perfil <span class="font-mono text-xs text-gray-400">{{ $role->name }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">
                {{ count($selectedPermissions) }}/{{ $totalPermissions }} selecionadas
            </span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Bulk Actions --}}
    <div class="flex items-center gap-2 mb-4">
        <button wire:click="selectAll" class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
            Selecionar Todas
        </button>
        <button wire:click="deselectAll" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
            Desmarcar Todas
        </button>
    </div>

    {{-- Permissions by Category --}}
    <div class="space-y-4">
        @foreach($permissionsByCategory as $categoria => $permissions)
            @php
                $categoryPermissionIds = collect($permissions)->pluck('id')->toArray();
                $selectedInCategory = count(array_intersect($categoryPermissionIds, $selectedPermissions));
                $totalInCategory = count($categoryPermissionIds);
                $allSelected = $selectedInCategory === $totalInCategory;
                $someSelected = $selectedInCategory > 0 && !$allSelected;
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: true }">
                {{-- Category Header --}}
                <div class="flex items-center justify-between px-5 py-3 bg-gray-50/80 border-b border-gray-100 cursor-pointer"
                    @click="open = !open">
                    <div class="flex items-center gap-3">
                        {{-- Toggle Category Checkbox --}}
                        <button type="button" wire:click.stop="toggleCategory('{{ $categoria }}')"
                            class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                                {{ $allSelected ? 'bg-blue-600 border-blue-600' : ($someSelected ? 'bg-blue-100 border-blue-400' : 'border-gray-300 hover:border-blue-400') }}">
                            @if($allSelected)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($someSelected)
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/>
                                </svg>
                            @endif
                        </button>

                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $categoriaIcons[$categoria] ?? 'M4 6h16M4 12h16M4 18h16' }}"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-700">
                            {{ $categoriaLabels[$categoria] ?? ucfirst($categoria) }}
                        </h3>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $allSelected ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $selectedInCategory }}/{{ $totalInCategory }}
                        </span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Permissions Grid --}}
                <div x-show="open" x-collapse>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($permissions as $permission)
                            @php $isSelected = in_array($permission->id, $selectedPermissions); @endphp
                            <label class="flex items-center gap-3 p-3 rounded-lg border transition-all cursor-pointer
                                {{ $isSelected ? 'border-blue-300 bg-blue-50/60' : 'border-gray-100 hover:bg-gray-50 hover:border-gray-200' }}">
                                <input type="checkbox"
                                    wire:click="togglePermission({{ $permission->id }})"
                                    {{ $isSelected ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">{{ $permission->getLabel() }}</span>
                                    <p class="text-xs text-gray-400 font-mono">{{ $permission->name }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
        <p class="text-sm text-gray-500">
            <span class="font-semibold text-gray-700">{{ count($selectedPermissions) }}</span> permissoes selecionadas
        </p>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.index') }}" wire:navigate
                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button wire:click="save"
                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Salvar Permissoes
            </button>
        </div>
    </div>
</div>
