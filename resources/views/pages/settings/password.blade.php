<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Palavra-passe')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password'         => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('toast', type: 'success', message: 'Palavra-passe atualizada com sucesso.');
        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout
        heading="Palavra-passe"
        subheading="Garanta que a sua conta usa uma palavra-passe segura e única"
    >
        {{-- ── Dicas de segurança ─────────────────────────── --}}
        <div class="flex items-start gap-3 p-3.5 mb-6 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/30 rounded-xl">
            <div class="shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </div>
            <div class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                <p class="font-semibold">Boas práticas de segurança</p>
                <ul class="space-y-0.5 text-blue-600 dark:text-blue-400/80 list-disc list-inside">
                    <li>Mínimo de 8 caracteres com letras, números e símbolos</li>
                    <li>Não reutilize palavras-passe de outros serviços</li>
                    <li>Evite informação pessoal como nome ou data de nascimento</li>
                </ul>
            </div>
        </div>

        {{-- ── Formulário ─────────────────────────────────── --}}
        <form method="POST" wire:submit="updatePassword" class="space-y-5">
            <flux:input
                wire:model="current_password"
                label="Palavra-passe actual"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />

            <div
                x-data="{
                    pw: '',
                    get strength() {
                        if (!this.pw) return 0;
                        let s = 0;
                        if (this.pw.length >= 8)  s++;
                        if (this.pw.length >= 12) s++;
                        if (/[A-Z]/.test(this.pw) && /[a-z]/.test(this.pw)) s++;
                        if (/[0-9]/.test(this.pw)) s++;
                        if (/[^A-Za-z0-9]/.test(this.pw)) s++;
                        return s;
                    },
                    get label()  { return ['','Fraca','Razoável','Boa','Forte','Muito forte'][this.strength] || '' },
                    get color()  { return ['','bg-red-500','bg-amber-500','bg-yellow-400','bg-emerald-500','bg-emerald-600'][this.strength] || 'bg-zinc-300' },
                    get textColor() { return ['','text-red-500','text-amber-500','text-yellow-600','text-emerald-600','text-emerald-600'][this.strength] || '' },
                }"
            >
                <flux:input
                    wire:model="password"
                    x-model="pw"
                    label="Nova palavra-passe"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />

                {{-- Indicador de força --}}
                <div x-show="pw.length > 0" class="mt-2 space-y-1.5" x-cloak>
                    <div class="flex gap-1">
                        <template x-for="i in 5">
                            <div
                                class="h-1 flex-1 rounded-full transition-all duration-300"
                                :class="i <= strength ? color : 'bg-zinc-200 dark:bg-zinc-700'"
                            ></div>
                        </template>
                    </div>
                    <p class="text-xs font-medium transition-colors" :class="textColor" x-text="label"></p>
                </div>
            </div>

            <flux:input
                wire:model="password_confirmation"
                label="Confirmar nova palavra-passe"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            />

            <div class="flex items-center justify-between pt-1">
                <flux:button variant="primary" type="submit" icon="lock-closed">
                    Atualizar palavra-passe
                </flux:button>

                <x-action-message on="password-updated" class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                    Guardado.
                </x-action-message>
            </div>
        </form>

        {{-- ── Sessões activas ─────────────────────────────── --}}
        <div class="mt-8 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Sessão actual</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Informação sobre a sua sessão de trabalho</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                    <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                </div>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Dispositivo</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300 text-xs truncate max-w-[180px]">
                        {{ str(request()->userAgent())->before('(') ?: 'Desconhecido' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Endereço IP</span>
                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded">
                        {{ request()->ip() }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Início da sessão</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">
                        {{ now()->translatedFormat('d M Y, H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
