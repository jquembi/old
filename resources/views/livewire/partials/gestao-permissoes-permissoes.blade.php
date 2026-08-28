<div class="space-y-6">
    {{-- Barra de Ações --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search"
                       wire:model.live.debounce.300ms="nomeBuscaPermissoes"
                       placeholder="Pesquisar permissões..."
                       class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div class="flex gap-2">
            <select wire:model.live="filtroGrupoPermissoes"
                    class="px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="todos">Todos os grupos</option>
                @foreach($gruposPermissoes as $grupo)
                    <option value="{{ $grupo }}">{{ ucfirst($grupo) }}</option>
                @endforeach
            </select>

            <button wire:click="$toggle('showFormNovaPermissao')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">Nova Permissão</span>
                <span class="sm:hidden">Nova</span>
            </button>
        </div>
    </div>

    {{-- Form Nova Permissão --}}
    @if($showFormNovaPermissao)
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 animate-fade-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Criar Nova Permissão
                </h3>
                <button wire:click="resetFormNovaPermissao"
                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nome da Permissão <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <input type="text"
                               wire:model="novaPermissao"
                               placeholder="Ex: criar_artigos"
                               class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Use snake_case (ex: gerir_utilizadores)
                    </p>
                    @error('novaPermissao')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Grupo
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <input type="text"
                               wire:model="grupoPermissao"
                               placeholder="Ex: artigos"
                               list="grupos-permissoes"
                               class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <datalist id="grupos-permissoes">
                            @foreach($gruposPermissoes as $grupo)
                                <option value="{{ $grupo }}">
                            @endforeach
                        </datalist>
                    </div>
                    @error('grupoPermissao')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Descrição <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <textarea wire:model="descricaoPermissao"
                                  rows="3"
                                  placeholder="Descreva o que esta permissão permite fazer..."
                                  class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    @error('descricaoPermissao')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Preview da Permissão --}}
            @if($novaPermissao && $descricaoPermissao)
                <div class="mt-4 p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Pré-visualização</p>
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $novaPermissao }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    {{ $grupoPermissao }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $descricaoPermissao }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="resetFormNovaPermissao"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </button>
                <button wire:click="criarPermissao"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Criar Permissão
                </button>
            </div>
        </div>
    @endif

    {{-- Lista de Permissões por Grupo --}}
    <div class="space-y-4">
        @forelse($permissoes as $grupo => $permissoesDoGrupo)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                 x-data="{ expanded: true }">
                {{-- Cabeçalho do Grupo --}}
                <button @click="expanded = !expanded"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                {{ ucfirst($grupo) }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $permissoesDoGrupo->count() }} permiss{{ $permissoesDoGrupo->count() !== 1 ? 'ões' : 'ão' }}
                            </p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transition-transform"
                         :class="{ 'rotate-180': !expanded }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Lista de Permissões do Grupo --}}
                <div x-show="expanded" x-collapse>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($permissoesDoGrupo as $permissao)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $permissao->nome }}
                                                </h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $permissao->descricao }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                {{-- Estatísticas de uso --}}
                                                @php
                                                    $totalRoles = $permissao->roles()->count();
                                                @endphp
                                                <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                                    {{ $totalRoles }} regra{{ $totalRoles !== 1 ? 's' : '' }}
                                                </span>

                                                {{-- Ações futuras podem ser adicionadas aqui --}}
                                                <button class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors"
                                                        title="Ver regras com esta permissão">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Tags de uso rápido --}}
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach($permissao->roles()->limit(3)->get() as $role)
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                    {{ $role->nome }}
                                                </span>
                                            @endforeach
                                            @if($totalRoles > 3)
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                    +{{ $totalRoles - 3 }} mais
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Nenhuma permissão encontrada
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">
                    @if($filtroGrupoPermissoes !== 'todos')
                        Nenhuma permissão no grupo "{{ ucfirst($filtroGrupoPermissoes) }}".
                    @else
                        Comece por criar a sua primeira permissão.
                    @endif
                </p>
                <button wire:click="$toggle('showFormNovaPermissao')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Primeira Permissão
                </button>
            </div>
        @endforelse
    </div>

    {{-- Dica Rápida --}}
    @if($permissoes->isNotEmpty())
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Dica:</strong> As permissões são atribuídas às regras na aba "Regras".
                        Selecione uma regra para gerir as suas permissões.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
