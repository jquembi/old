<div>
    {{-- SECTION: Bloqueio de segurança --}}
    @if($bloqueado ?? false)
        <div class="px-4 md:px-6 lg:px-8 py-8">
            <div class="card p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-red-600 dark:text-red-400 mb-1">Acesso Restrito</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $motivo ?? 'Acesso não autorizado.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
    <div class="px-4 md:px-6 lg:px-8 py-8 space-y-6">

        {{-- SECTION: Cabeçalho --}}
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">Utilizadores</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Gerir utilizadores, funções e permissões de acesso</p>
            </div>
            @if(auth()->user()?->temPermissao('usuarios.criar'))
                <a href="{{ route('usuarios.criar') }}" wire:navigate>
                    <flux:button variant="primary" icon="plus">Novo Utilizador</flux:button>
                </a>
            @endif
        </div>

        {{-- SECTION: Filtros --}}
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Pesquisar por nome ou email..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filtroRoleId" class="w-44">
                <flux:select.option value="">Todas as funções</flux:select.option>
                @foreach($roles->groupBy('grupo') as $grupo => $grupoRoles)
                    <optgroup label="{{ $grupo }}">
                        @foreach($grupoRoles as $role)
                            <flux:select.option value="{{ $role->id }}">{{ $role->descricao }}</flux:select.option>
                        @endforeach
                    </optgroup>
                @endforeach
            </flux:select>
            @if($municipios->isNotEmpty())
                <flux:select wire:model.live="filtroMunicipioId" class="w-44">
                    <flux:select.option value="">Todos os municípios</flux:select.option>
                    @foreach($municipios as $mun)
                        <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>

        {{-- SECTION: Tabela --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Utilizador</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Função</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider hidden md:table-cell">Município</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider hidden lg:table-cell">Registado em</th>
                        @if(auth()->user()?->temPermissao('usuarios.criar'))
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Acções</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                    @forelse($usuarios as $usuario)
                        <tr wire:key="usuario-row-{{ $usuario->id }}" class="transition-colors hover:bg-red-50/40 dark:hover:bg-red-950/10">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar :initials="Str::of($usuario->name)->explode(' ')->take(2)->map(fn ($w) => Str::substr($w, 0, 1))->implode('')" size="sm" />
                                    <div>
                                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">{{ $usuario->name }}</div>
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $usuario->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($usuario->roles as $role)
                                        <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400 border border-red-200/60 dark:border-red-800/40">
                                            {{ $role->descricao }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">Sem função</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 hidden md:table-cell">{{ $usuario->municipio?->nome ?? $usuario->provincia?->nome ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($usuario->activo)
                                    <span class="badge bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Activo</span>
                                @else
                                    <span class="badge badge-zinc">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400 hidden lg:table-cell">{{ $usuario->created_at->format('d/m/Y') }}</td>
                            @if(auth()->user()?->temPermissao('usuarios.criar'))
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('usuarios.editar', $usuario) }}" wire:navigate>
                                        <flux:button size="sm" variant="ghost" icon="pencil" />
                                    </a>
                                    <flux:button wire:click="toggleActivo({{ $usuario->id }})" size="sm" variant="ghost"
                                        icon="{{ $usuario->activo ? 'x-circle' : 'check-circle' }}"
                                        class="{{ $usuario->activo ? 'text-red-500' : 'text-red-500' }}" />
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->temPermissao('usuarios.criar') ? 6 : 5 }}" class="px-4 py-14 text-center">
                                <svg class="w-10 h-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nenhum utilizador encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        @if($usuarios->hasPages())
            <div class="mt-2">{{ $usuarios->links() }}</div>
        @endif

    </div>
    @endif
</div>
