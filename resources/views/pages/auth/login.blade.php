<x-layouts::auth :title="__('Iniciar Sessão')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Iniciar Sessão')" :description="__('Introduza as suas credenciais para aceder ao sistema')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Endereço de email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="utilizador@exemplo.co.ao"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Palavra-passe')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Palavra-passe')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Esqueceu a palavra-passe?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Manter sessão iniciada')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Entrar') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Não tem conta?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Criar conta') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
