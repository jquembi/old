<x-layouts::auth :title="__('Confirmar Palavra-passe')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirmar Palavra-passe')"
            :description="__('Esta é uma área segura da aplicação. Por favor, confirme a sua palavra-passe antes de continuar.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Palavra-passe')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Palavra-passe')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirmar') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
