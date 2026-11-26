<x-layouts::auth :title="__('Iniciar Sessão — G.TERRAS')">
    <div class="flex flex-col gap-6">
        {{-- Cabeçalho do Login --}}
        <div class="space-y-2 text-left">
            <p class="text-xs font-bold uppercase tracking-[.16em] text-[#C30E20]">Conta institucional</p>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-white">Iniciar sessão</h1>
        </div>

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
    </div>
</x-layouts::auth>
