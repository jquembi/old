<div class="space-y-6">
    {{-- Barra de Ações --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search"
                       wire:model.live.debounce.300ms="nomeBuscaRoles"
                       placeholder="Pesquisar regras..."
                       class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <a href="{{ route('permissoes.regra.criar') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden sm:inline">Nova Regra</span>
            <span class="sm:hidden">Nova</span>
        </a>
    </div>

    {{-- Lista de Regras --}}
    <div class="space-y-4">
        @forelse($roles as $grupo => $regrasDoGrupo)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ $grupo }}
                        <span class="ml-2 text-xs font-normal text-gray-500 dark:text-gray-400">
                            {{ $regrasDoGrupo->count() }} regra{{ $regrasDoGrupo->count() !== 1 ? 's' : '' }}
                        </span>
                    </h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($regrasDoGrupo as $role)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                             :class="{ 'bg-primary-50 dark:bg-primary-900/20': {{ $roleEmEdicao === $role->id ? 'true' : 'false' }} }">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                {{-- Info da Regra --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start gap-3">
                                        <button wire:click="selecionarRole({{ $role->id }})"
                                                class="flex-shrink-0 mt-1">
                                            <div class="w-5 h-5 rounded border-2 {{ $roleEmEdicao === $role->id ? 'bg-primary-500 border-primary-500' : 'border-gray-300 dark:border-gray-600' }} flex items-center justify-center">
                                                @if($roleEmEdicao === $role->id)
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </button>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                                    {{ $role->nome }}
                                                </h4>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $this->nivelBadgeColor[$role->nivel] ?? 'gray' }}-100 dark:bg-{{ $this->nivelBadgeColor[$role->nivel] ?? 'gray' }}-900/30 text-{{ $this->nivelBadgeColor[$role->nivel] ?? 'gray' }}-700 dark:text-{{ $this->nivelBadgeColor[$role->nivel] ?? 'gray' }}-300">
                                                    Nível {{ $role->nivel }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                {{ $role->descricao }}
                                            </p>

                                            <div class="flex flex-wrap items-center gap-3 mt-2">
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                    {{ $role->users_count }} utilizador{{ $role->users_count !== 1 ? 'es' : '' }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $role->permissoes_count }} permiss{{ $role->permissoes_count !== 1 ? 'ões' : 'ão' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Ações --}}
                                <div class="flex items-center gap-1 sm:flex-shrink-0">
                                    <button wire:click="verUsuariosComRole({{ $role->id }})"
                                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors"
                                            title="Ver utilizadores com esta regra">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </button>

                                    <button wire:click="selecionarRole({{ $role->id }})"
                                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors"
                                            title="Gerir permissões">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <a href="{{ route('permissoes.regra.editar', $role) }}" wire:navigate
                                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                            title="Editar regra">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>

                                    <button wire:click="confirmarEliminarRegra({{ $role->id }})"
                                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                            title="Eliminar regra">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Painel de Permissões (expandido) --}}
                            @if($roleEmEdicao === $role->id)
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Gerir Permissões
                                        </h5>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ count($permissoesSelectedRole) }} selecionadas
                                        </span>
                                    </div>

                                    <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                                        @foreach($permissoes as $grupo => $permissoesDoGrupo)
                                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h6 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                                        {{ $grupo }}
                                                    </h6>
                                                    <button wire:click="toggleTodasPermissoesDoGrupo('{{ $grupo }}', {{ json_encode($permissoesDoGrupo->pluck('id')->toArray()) }})"
                                                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                                        Selecionar Todas
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                    @foreach($permissoesDoGrupo as $permissao)
                                                        <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                                            <input type="checkbox"
                                                                   wire:model.live="permissoesSelectedRole"
                                                                   value="{{ $permissao->id }}"
                                                                   class="mt-0.5 w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    {{ $permissao->nome }}
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                                                    {{ $permissao->descricao }}
                                                                </p>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end mt-4">
                                        <button wire:click="atualizarPermissoesRole"
                                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                                            Guardar Alterações
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nenhuma regra encontrada</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">
                    {{ $nomeBuscaRoles ? 'Nenhuma regra corresponde à sua pesquisa.' : 'Comece por criar a sua primeira regra.' }}
                </p>
                @if(!$nomeBuscaRoles)
                    <a href="{{ route('permissoes.regra.criar') }}" wire:navigate
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Criar Primeira Regra
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>
