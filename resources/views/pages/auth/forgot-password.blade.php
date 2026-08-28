<x-layouts::auth :title="__('Recuperar Palavra-passe')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Recuperar Palavra-passe')" :description="__('Introduza o seu email para receber o link de recuperação')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Endereço de email')"
                type="email"
                required
                autofocus
                placeholder="utilizador@exemplo.co.ao"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Enviar link de recuperação') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Ou voltar para') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('iniciar sessão') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
