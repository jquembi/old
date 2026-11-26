<x-layouts::auth :title="__('Verificação de Email')">
    <div class="mt-4 flex flex-col gap-6">
        <x-auth-header :title="__('Verificação de Email')" :description="__('Verifique o seu endereço de email clicando no link que acabámos de enviar.')" />

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Reenviar email de verificação') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                    {{ __('Terminar sessão') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
