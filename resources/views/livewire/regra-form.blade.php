<div class="px-4 md:px-6 lg:px-8 py-8 max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('permissoes.index') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">Permissões</a>
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-zinc-700 dark:text-zinc-200 font-medium">{{ $registoId ? 'Editar Regra' : 'Nova Regra' }}</span>
    </nav>

    {{-- Título --}}
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">
            {{ $registoId ? 'Editar Regra' : 'Nova Regra' }}
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Define o nome, descrição, nível de acesso e grupo da regra.
        </p>
    </div>

    {{-- Formulário --}}
    <div class="card p-6 space-y-5">

        {{-- Nome --}}
        <div>
            <flux:input wire:model="nome" label="Nome da Regra" placeholder="Ex: gestor_conteudo" required />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Use snake_case (ex: admin_municipal)</p>
        </div>

        {{-- Descrição --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                Descrição <span class="text-red-500">*</span>
            </label>
            <textarea wire:model="descricao" rows="3"
                placeholder="Descreva o propósito desta regra..."
                class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors resize-none">
            </textarea>
            @error('descricao') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Nível de Acesso --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                Nível de Acesso <span class="text-red-500">*</span>
            </label>
            <select wire:model="nivel"
                class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors">
                <option value="1">1 — INOTU (Acesso Total)</option>
                <option value="2">2 — Governo Provincial</option>
                <option value="3">3 — Administração Municipal</option>
                <option value="4">4 — Técnico (Acesso Limitado)</option>
            </select>
            @error('nivel') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Grupo --}}
        <div>
            <flux:input wire:model="grupo" label="Grupo" placeholder="Ex: INOTU, Governo Provincial, Administração Municipal" required />
            @error('grupo') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
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
            <a href="{{ route('permissoes.index') }}" wire:navigate>
                <flux:button variant="ghost">Cancelar</flux:button>
            </a>
            <flux:button wire:click="guardar" variant="primary">
                {{ $registoId ? 'Guardar Alterações' : 'Criar Regra' }}
            </flux:button>
        </div>
    </div>
</div>
