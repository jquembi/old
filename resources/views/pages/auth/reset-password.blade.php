<x-layouts::auth :title="__('Redefinir Palavra-passe')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Redefinir Palavra-passe')" :description="__('Introduza a sua nova palavra-passe abaixo')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Email')"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Nova palavra-passe')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Nova palavra-passe')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmar palavra-passe')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirmar palavra-passe')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Redefinir palavra-passe') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
