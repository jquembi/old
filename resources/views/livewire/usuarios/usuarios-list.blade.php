<div>
    @section('page-title', 'Gestao de Usuarios')

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">Usuarios</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Gerir contas, roles e acessos dos usuarios da plataforma</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Usuario
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome, email ou BI..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
                <select wire:model.live="roleFilter" class="w-full sm:w-44 py-2.5 px-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    <option value="">Todos os roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->getLabel() }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter" class="w-full sm:w-36 py-2.5 px-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    <option value="">Todos os status</option>
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/40 border-b border-slate-200 dark:border-slate-700/60">
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Usuario</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Role</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Municipio</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Ultimo Acesso</th>
                            <th class="px-5 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                        @forelse($usuarios as $usuario)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-emerald-600 text-white rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0">
                                            {{ $usuario->initials() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-800 dark:text-white truncate">{{ $usuario->name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ $usuario->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @foreach($usuario->roles as $role)
                                        @php
                                            $roleColor = match($role->name) {
                                                'inotu' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'governo_provincial' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'tecnico_municipal' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                                            };
                                        @endphp
                                        <span class="inline-flex text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $roleColor }}">
                                            {{ $role->getLabel() }}
                                        </span>
                                    @endforeach
                                    @if($usuario->roles->isEmpty())
                                        <span class="text-xs text-slate-400">Sem role</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 text-xs">
                                    {{ $usuario->municipio?->nome ?? '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($usuario->is_active)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Ativo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $usuario->last_login_at ? \Carbon\Carbon::parse($usuario->last_login_at)->diffForHumans() : 'Nunca' }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if($usuario->id !== auth()->id())
                                            <button wire:click="toggleActive({{ $usuario->id }})"
                                                    class="p-1.5 rounded-lg transition {{ $usuario->is_active ? 'text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' }}"
                                                    title="{{ $usuario->is_active ? 'Desativar' : 'Ativar' }}">
                                                @if($usuario->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                            <button wire:click="deleteUser({{ $usuario->id }})"
                                                    wire:confirm="Tem certeza que deseja eliminar este usuario?"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum usuario encontrado</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($usuarios->hasPages())
            <div class="flex justify-center">{{ $usuarios->links() }}</div>
        @endif
    </div>
</div>
