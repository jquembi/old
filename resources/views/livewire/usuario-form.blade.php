<div class="px-4 md:px-6 lg:px-8 py-8 max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('usuarios.index') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">Utilizadores</a>
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-zinc-700 dark:text-zinc-200 font-medium">{{ $registoId ? 'Editar Utilizador' : 'Novo Utilizador' }}</span>
    </nav>

    {{-- Título --}}
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">
            {{ $registoId ? 'Editar Utilizador' : 'Novo Utilizador' }}
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Preencha os dados do utilizador. Apenas Administradores INOTU podem criar ou editar utilizadores.
        </p>
    </div>

    {{-- Formulário --}}
    <div class="card p-6 space-y-5">

        {{-- Nome --}}
        <flux:input wire:model="name" label="Nome Completo" placeholder="Ex: João Silva" required />

        {{-- Email --}}
        <flux:input wire:model="email" label="Email" type="email" placeholder="utilizador@inotu.ao" required />

        {{-- Palavra-passe --}}
        @if(!$registoId)
            <flux:input wire:model="password" label="Palavra-passe" type="password" required />
            <flux:input wire:model="password_confirmation" label="Confirmar Palavra-passe" type="password" required />
        @else
            <flux:input wire:model="password" label="Nova Palavra-passe (opcional)" type="password" placeholder="Deixar em branco para não alterar" />
            <flux:input wire:model="password_confirmation" label="Confirmar Nova Palavra-passe" type="password" />
        @endif

        {{-- Funções --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                Funções <span class="text-red-500">*</span>
            </label>
            <div class="max-h-52 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700 p-2 space-y-1 bg-white dark:bg-zinc-900">
                @foreach($roles->groupBy('grupo') as $grupo => $grupoRoles)
                    <div class="mb-2">
                        <div class="text-[11px] font-semibold uppercase text-zinc-400 dark:text-zinc-500 px-1 mb-1">{{ $grupo }}</div>
                        @foreach($grupoRoles as $role)
                            <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                                <input type="checkbox" wire:model.live="role_ids" value="{{ $role->id }}"
                                    class="rounded border-zinc-300 text-red-600 focus:ring-red-500 dark:border-zinc-600">
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $role->descricao }}</span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>
            @error('role_ids') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Província (fixa Huíla) --}}
        <flux:input label="Província" value="Huíla" disabled />

        {{-- Município --}}
        @if($municipios->isNotEmpty())
            <flux:select wire:model="municipio_id" label="Município">
                <flux:select.option value="">Nenhum (Nível provincial)</flux:select.option>
                @foreach($municipios as $mun)
                    <flux:select.option value="{{ $mun->id }}">{{ $mun->nome }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif

        {{-- Activo --}}
        <div class="flex items-center gap-3 py-1">
            <input type="checkbox" wire:model="activo" id="activo-check"
                class="w-4 h-4 rounded border-zinc-300 text-red-600 focus:ring-red-500 dark:border-zinc-600">
            <label for="activo-check" class="text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer">
                Utilizador activo
            </label>
        </div>

        {{-- Erros gerais --}}
        @if($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Acções --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <a href="{{ route('usuarios.index') }}" wire:navigate>
                <flux:button variant="ghost">Cancelar</flux:button>
            </a>
            <flux:button wire:click="guardar" variant="primary">
                {{ $registoId ? 'Guardar Alterações' : 'Criar Utilizador' }}
            </flux:button>
        </div>
    </div>
</div>
