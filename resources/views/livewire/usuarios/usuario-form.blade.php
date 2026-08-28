<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.usuarios.index') }}" wire:navigate
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ $usuarioId ? 'Editar Usuario' : 'Novo Usuario' }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $usuarioId ? 'Atualize os dados do usuario' : 'Preencha os dados para criar um novo usuario' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Dados Pessoais --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Dados Pessoais</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nome --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                    <input type="text" wire:model="name"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 @enderror"
                        placeholder="Nome do usuario">
                    @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" wire:model="email"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror"
                        placeholder="email@exemplo.com">
                    @error('email') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- BI --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BI / Documento</label>
                    <input type="text" wire:model="bi"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('bi') border-red-500 @enderror"
                        placeholder="Numero do BI">
                    @error('bi') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Telefone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" wire:model="telefone"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="+244 9XX XXX XXX">
                </div>

                {{-- Cargo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                    <input type="text" wire:model="cargo"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Cargo ou funcao">
                </div>

                {{-- Instituicao --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instituicao</label>
                    <input type="text" wire:model="instituicao"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Instituicao ou orgao">
                </div>
            </div>
        </div>

        {{-- Senha --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                    {{ $usuarioId ? 'Alterar Senha (opcional)' : 'Senha *' }}
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $usuarioId ? 'Nova Senha' : 'Senha *' }}
                    </label>
                    <input type="password" wire:model="password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-500 @enderror"
                        placeholder="Minimo 8 caracteres">
                    @error('password') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
                    <input type="password" wire:model="password_confirmation"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Repita a senha">
                </div>
            </div>
        </div>

        {{-- Acesso e Permissoes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Acesso e Permissoes</h2>
            </div>
            <div class="p-6 space-y-5">
                {{-- Municipio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                    <select wire:model="municipio_id"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="">Todos os municipios</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Administradores INOTU podem acessar todos os municipios.</p>
                </div>

                {{-- Roles --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Roles / Perfis *</label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            <label class="flex items-center gap-3 p-3 rounded-lg border transition-colors cursor-pointer
                                {{ in_array($role->id, $selectedRoles) ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $role->getLabel() }}</span>
                                    @if($role->description)
                                        <p class="text-xs text-gray-500">{{ $role->description }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $role->permissions->count() }} permissoes atribuidas
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedRoles') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50/50">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Conta Ativa</span>
                        <p class="text-xs text-gray-500">Usuarios inativos nao podem acessar a plataforma.</p>
                    </div>
                    <button type="button" wire:click="$toggle('is_active')"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Acoes --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.usuarios.index') }}" wire:navigate
                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $usuarioId ? 'Atualizar Usuario' : 'Criar Usuario' }}
            </button>
        </div>
    </form>
</div>
