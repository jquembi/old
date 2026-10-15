<div class="flex items-start max-md:flex-col gap-0">
    {{-- ── Sidebar de Navegação ────────────────────────── --}}
    <div class="shrink-0 w-full md:w-56 pb-4 md:pb-0 md:me-10">
        {{-- Mini-card do utilizador --}}
        <div class="flex items-center gap-3 px-3 py-3 mb-4 rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800">
            @php $u = auth()->user(); @endphp
            @if ($u->foto_perfil)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($u->foto_perfil) }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-red-500/20 shrink-0" />
            @else
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-red-600 to-red-500 flex items-center justify-center ring-1 ring-red-500/20 shrink-0">
                    <span class="text-xs font-bold text-white select-none">{{ $u->initials() }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $u->name }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $u->roles->pluck('descricao')->implode(', ') ?: 'Utilizador' }}</p>
            </div>
        </div>

        {{-- Navegação --}}
        <flux:navlist aria-label="Definições">
            <flux:navlist.group heading="Perfil" class="md:hidden">
                <flux:navlist.item :href="route('profile.overview')" wire:navigate icon="user-circle">
                    Visão Geral
                </flux:navlist.item>
                <flux:navlist.item :href="route('profile.edit')" wire:navigate icon="pencil-square">
                    Editar Perfil
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.item :href="route('profile.overview')" wire:navigate icon="user-circle" class="max-md:hidden">
                Visão Geral
            </flux:navlist.item>
            <flux:navlist.item :href="route('profile.edit')" wire:navigate icon="pencil-square" class="max-md:hidden">
                Editar Perfil
            </flux:navlist.item>

            <flux:navlist.group heading="Segurança">
                <flux:navlist.item :href="route('user-password.edit')" wire:navigate icon="lock-closed">
                    Palavra-passe
                </flux:navlist.item>
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <flux:navlist.item :href="route('two-factor.show')" wire:navigate icon="shield-check">
                        Autenticação 2FA
                    </flux:navlist.item>
                @endif
            </flux:navlist.group>

            <flux:navlist.group heading="Preferências">
                <flux:navlist.item :href="route('appearance.edit')" wire:navigate icon="swatch">
                    Aparência
                </flux:navlist.item>
                @if(auth()->user()->isNacional() || auth()->user()->isInotu())
                    <flux:navlist.item :href="route('settings.qr-layouts')" wire:navigate icon="qr-code">
                        Layouts de QR Code
                    </flux:navlist.item>
                @endif
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden my-4" />

    {{-- ── Conteúdo ──────────────────────────────────────── --}}
    <div class="flex-1 self-stretch min-w-0">
        @if(isset($heading) && $heading)
            <div class="mb-5">
                <flux:heading size="lg">{{ $heading }}</flux:heading>
                @if(isset($subheading) && $subheading)
                    <flux:subheading>{{ $subheading }}</flux:subheading>
                @endif
            </div>
        @endif

        <div class="w-full {{ ($wide ?? false) ? '' : 'max-w-lg' }}">
            {{ $slot }}
        </div>
    </div>
</div>
