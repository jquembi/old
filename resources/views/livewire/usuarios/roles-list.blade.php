<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Roles e Permissoes</h1>
            <p class="text-sm text-gray-500 mt-1">Gerencie os perfis de acesso e suas permissoes</p>
        </div>
    </div>

    {{-- Roles Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($roles as $role)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6">
                    {{-- Role Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $role->name === 'inotu' ? 'bg-red-100 text-red-600' : ($role->name === 'governo_provincial' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600') }}">
                            @if($role->name === 'inotu')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @elseif($role->name === 'governo_provincial')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">{{ $role->getLabel() }}</h3>
                            <span class="text-xs text-gray-400 font-mono">{{ $role->name }}</span>
                        </div>
                    </div>

                    @if($role->description)
                        <p class="text-sm text-gray-600 mb-4">{{ $role->description }}</p>
                    @endif

                    {{-- Stats --}}
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center gap-1.5 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                            <span>{{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <span>{{ $role->permissions_count }} permissoes</span>
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('admin.roles.edit', $role) }}" wire:navigate
                        class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Gerenciar Permissoes
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
