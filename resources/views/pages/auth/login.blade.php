<x-layouts::auth :title="__('Iniciar Sessão — G.TERRAS')">
    <div class="flex flex-col gap-6">
        {{-- Cabeçalho do Login --}}
        <div class="text-center sm:text-left space-y-1">
            <div class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-950/40 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
                <flux:icon.lock-closed class="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                <span>Acesso Seguro ao Portal</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Iniciar Sessão</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Introduza as suas credenciais institucionais para aceder ao sistema.</p>
        </div>

        {{-- Status da Sessão --}}
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- Resumo de Erros Visível se houver --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50/80 p-3.5 text-xs text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 animate-shake">
                <div class="flex items-start gap-2.5">
                    <flux:icon.exclamation-triangle class="h-4 w-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                        <span class="font-semibold block">Falha na Autenticação</span>
                        @foreach ($errors->all() as $error)
                            <p class="leading-relaxed">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Formulário de Autenticação --}}
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            {{-- Endereço de Email --}}
            <div>
                <flux:input
                    name="email"
                    :label="__('Endereço de Email')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="exemplo@gterras.gov.ao"
                    icon="envelope"
                />
            </div>

            {{-- Palavra-passe --}}
            <div class="relative space-y-1">
                <div class="flex items-center justify-between">
                    <flux:input
                        name="password"
                        :label="__('Palavra-passe')"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        icon="key"
                        viewable
                        class="w-full"
                    />
                </div>

                @if (Route::has('password.request'))
                    <div class="flex justify-end pt-1">
                        <flux:link class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium" :href="route('password.request')" wire:navigate>
                            {{ __('Esqueceu a palavra-passe?') }}
                        </flux:link>
                    </div>
                @endif
            </div>

            {{-- Lembrar-me --}}
            <div class="pt-1">
                <flux:checkbox name="remember" :label="__('Manter sessão iniciada neste dispositivo')" :checked="old('remember')" />
            </div>

            {{-- Botão de Submissão --}}
            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 shadow-md shadow-red-600/20" data-test="login-button">
                    <flux:icon.arrow-right class="h-4 w-4 mr-1.5" />
                    {{ __('Entrar no Sistema') }}
                </flux:button>
            </div>
        </form>

        {{-- Informações de Apoio ao Utilizador --}}
        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 text-center">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Precisa de assistência ou autorização de acesso?
            </p>
            <p class="text-xs text-zinc-600 dark:text-zinc-300 font-medium mt-1">
                Contacte o Administrador Provincial ou Municipal do INOTU.
            </p>
        </div>
    </div>
</x-layouts::auth>
