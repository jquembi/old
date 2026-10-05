<div class="gestao-permissoes px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

    {{-- Cabeçalho com Stats --}}
    <div class="mb-6">
        <x-ui.page-header eyebrow="Gestão" title="Gestão de Permissões" description="Gerir regras de acesso, permissões e atribuições de utilizadores" />

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Total de Regras</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_regras'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Permissões</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_permissoes'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Utilizadores</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['total_usuarios_com_regras'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 shadow-sm border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Regras Vazias</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats['regras_sem_permissoes'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($bloqueado ?? false)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H9m3-4V8m0 0V6m0 2h2m-2 0H9m3 4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-2">Acesso Restrito</h3>
            <p class="text-red-600 dark:text-red-400">{{ $motivo }}</p>
        </div>
    @else
        {{-- Tabs de Navegação --}}
        <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
            <nav class="flex flex-wrap items-center gap-2 -mb-px px-1">
                <button type="button" wire:click.prevent="setAbaAtiva('regras')" wire:key="tab-regras"
                        class="relative group px-4 py-3 text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors duration-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Regras</span>
                    </div>
                    @if($abaAtiva === 'regras')
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-red-500 via-red-600 to-red-500 rounded-full"></div>
                    @else
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-zinc-200 dark:bg-zinc-700 group-hover:h-0.5 group-hover:bg-zinc-300 dark:group-hover:bg-zinc-600 transition-all"></div>
                    @endif
                </button>

                <button type="button" wire:click.prevent="setAbaAtiva('permissoes')" wire:key="tab-permissoes"
                        class="relative group px-4 py-3 text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors duration-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Permissões</span>
                    </div>
                    @if($abaAtiva === 'permissoes')
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-red-500 via-red-600 to-red-500 rounded-full"></div>
                    @else
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-zinc-200 dark:bg-zinc-700 group-hover:h-0.5 group-hover:bg-zinc-300 dark:group-hover:bg-zinc-600 transition-all"></div>
                    @endif
                </button>

                <button type="button" wire:click.prevent="setAbaAtiva('utilizadores')" wire:key="tab-utilizadores"
                        class="relative group px-4 py-3 text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors duration-200 inline-flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Utilizadores</span>
                        @if($usuariosEmEdicao)
                            <span class="ml-1 px-1.5 py-0.5 text-xs font-bold bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-full">
                                {{ $usuariosComRole->total() ?? 0 }}
                            </span>
                        @endif
                    </div>
                    @if($abaAtiva === 'utilizadores')
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-red-500 via-red-600 to-red-500 rounded-full"></div>
                    @else
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-zinc-200 dark:bg-zinc-700 group-hover:h-0.5 group-hover:bg-zinc-300 dark:group-hover:bg-zinc-600 transition-all"></div>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Conteúdo das Tabs --}}
        <div class="space-y-6" wire:key="aba-conteudo-{{ $abaAtiva }}">
            @if($abaAtiva === 'regras')
                @include('livewire.partials.gestao-permissoes-regras')
            @elseif($abaAtiva === 'permissoes')
                @include('livewire.partials.gestao-permissoes-permissoes')
            @elseif($abaAtiva === 'utilizadores')
                @include('livewire.partials.gestao-permissoes-utilizadores')
            @endif
        </div>
    @endif

    {{-- Modal de Confirmação Eliminar Regra --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
                     wire:click="fecharModals"></div>

                <div class="relative bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                            Confirmar Eliminação
                        </h3>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-6">
                            Tem certeza que deseja eliminar esta regra permanentemente?
                            Esta ação não pode ser desfeita.
                        </p>

                        <div class="flex justify-center gap-3">
                            <button wire:click="fecharModals"
                                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button wire:click="eliminarRegra"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                Eliminar Regra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Confirmação Revogar Regra --}}
    @if($showRevokeModal && $revokeData)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
                     wire:click="fecharModals"></div>

                <div class="relative bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                            Revogar Regra
                        </h3>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-6">
                            Revogar a regra <strong>{{ $revokeData['role_nome'] }}</strong>
                            do utilizador <strong>{{ $revokeData['usuario_nome'] }}</strong>?
                        </p>

                        <div class="flex justify-center gap-3">
                            <button wire:click="fecharModals"
                                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button wire:click="revogarRegraDoUsuario"
                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
