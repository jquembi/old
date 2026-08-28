<x-layouts::auth :title="__('Autenticação de Dois Factores')">
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $dispatch('clear-2fa-auth-code');

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code');
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Código de Autenticação')"
                    :description="__('Introduza o código de autenticação fornecido pela sua aplicação de autenticação.')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Código de Recuperação')"
                    :description="__('Confirme o acesso à sua conta introduzindo um dos seus códigos de recuperação de emergência.')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-5">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="Código OTP"
                                label:sr-only
                                class="mx-auto"
                             />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-5">
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                            />
                        </div>

                        @error('recovery_code')
                            <flux:text color="red">
                                {{ $message }}
                            </flux:text>
                        @enderror
                    </div>

                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                    >
                        {{ __('Continuar') }}
                    </flux:button>
                </div>

                <div class="mt-5 space-x-0.5 text-sm leading-5 text-center">
                    <span class="opacity-50">{{ __('ou pode') }}</span>
                    <div class="inline font-medium underline cursor-pointer opacity-80">
                        <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('usar um código de recuperação') }}</span>
                        <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('usar um código de autenticação') }}</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts::auth>
