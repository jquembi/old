<div class="space-y-6">
    @if(!$usuariosEmEdicao)
        {{-- Estado Inicial - Nenhuma regra selecionada --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 sm:p-12 text-center">
            <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Visualizar Utilizadores por Regra
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
                Selecione uma regra na aba "Regras" e clique no ícone
                <svg class="inline w-4 h-4 mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                para ver todos os utilizadores que a possuem.
            </p>

            <button wire:click="setAbaAtiva('regras')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Ir para Regras
            </button>
        </div>
    @else
        @php
            $roleSelecionada = \App\Models\Role::find($usuariosEmEdicao);
        @endphp

        {{-- Cabeçalho com info da regra --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $roleSelecionada->nome }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $roleSelecionada->descricao }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 text-sm bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-lg">
                        <strong>{{ $usuariosComRole->total() }}</strong> utilizador{{ $usuariosComRole->total() !== 1 ? 'es' : '' }}
                    </span>

                    <button wire:click="verUsuariosComRole({{ $usuariosEmEdicao }})"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            title="Fechar visualização">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Barra de Pesquisa --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                           wire:model.live.debounce.300ms="nomeBuscaUsuarios"
                           placeholder="Pesquisar por nome ou email..."
                           class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            @if($nomeBuscaUsuarios)
                <button wire:click="$set('nomeBuscaUsuarios', '')"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpar
                </button>
            @endif
        </div>

        {{-- Lista de Utilizadores --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($usuariosComRole->count() > 0)
                {{-- Cabeçalho da Tabela (Desktop) --}}
                <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <div class="col-span-4">Utilizador</div>
                    <div class="col-span-3">Email</div>
                    <div class="col-span-3">Outras Regras</div>
                    <div class="col-span-2 text-right">Ações</div>
                </div>

                {{-- Lista de Utilizadores --}}
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($usuariosComRole as $usuario)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                            {{-- Mobile View --}}
                            <div class="sm:hidden space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $usuario->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $usuario->email }}
                                            </p>
                                        </div>
                                    </div>

                                    <button wire:click="confirmarRevogarRegra({{ $usuario->id }})"
                                            class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-1">
                                    @foreach($usuario->roles->where('id', '!=', $usuariosEmEdicao) as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                            {{ $role->nome }}
                                        </span>
                                    @endforeach
                                    @if($usuario->roles->count() === 1)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                            Nenhuma outra regra
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Desktop View --}}
                            <div class="hidden sm:grid sm:grid-cols-12 sm:items-center gap-3">
                                <div class="col-span-4 flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $usuario->name }}
                                        </p>
                                    </div>
                                </div>

                                <div class="col-span-3">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $usuario->email }}
                                    </p>
                                </div>

                                <div class="col-span-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($usuario->roles->where('id', '!=', $usuariosEmEdicao) as $role)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                {{ $role->nome }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                Nenhuma
                                            </span>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="col-span-2 flex justify-end">
                                    <button wire:click="confirmarRevogarRegra({{ $usuario->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Revogar Regra
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginação --}}
                @if($usuariosComRole->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $usuariosComRole->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Nenhum utilizador encontrado
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        @if($nomeBuscaUsuarios)
                            Nenhum utilizador corresponde à sua pesquisa.
                        @else
                            Nenhum utilizador possui esta regra.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        {{-- Estatísticas da Regra --}}
        @if($usuariosComRole->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total de Utilizadores</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $usuariosComRole->total() }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Permissões da Regra</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $roleSelecionada->permissoes()->count() }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Nível de Acesso</p>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $this->nivelBadgeColor[$roleSelecionada->nivel] ?? 'gray' }}-100 dark:bg-{{ $this->nivelBadgeColor[$roleSelecionada->nivel] ?? 'gray' }}-900/30 text-{{ $this->nivelBadgeColor[$roleSelecionada->nivel] ?? 'gray' }}-700 dark:text-{{ $this->nivelBadgeColor[$roleSelecionada->nivel] ?? 'gray' }}-300">
                            Nível {{ $roleSelecionada->nivel }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $this->nivelLabel[$roleSelecionada->nivel] ?? 'Desconhecido' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
