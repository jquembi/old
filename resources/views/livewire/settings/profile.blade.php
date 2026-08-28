<div>
    @include('partials.settings-heading')

    <x-pages::settings.layout
        heading="Editar Perfil"
        subheading="Actualize a sua informação pessoal e fotografia de perfil"
    >
        {{-- ── Fotografia de Perfil ─────────────────────────── --}}
        <div class="flex items-start gap-5 p-5 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-800 mb-7">
            {{-- Avatar --}}
            <div class="relative shrink-0">
                @if ($user->foto_perfil)
                    <img
                        src="{{ Storage::url($user->foto_perfil) }}"
                        alt="{{ $user->name }}"
                        class="w-20 h-20 rounded-full object-cover ring-2 ring-red-500/30 ring-offset-2 ring-offset-zinc-50 dark:ring-offset-zinc-900"
                    />
                @else
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-600 to-red-500 flex items-center justify-center ring-2 ring-red-500/30 ring-offset-2 ring-offset-zinc-50 dark:ring-offset-zinc-900">
                        <span class="text-2xl font-bold text-white select-none">
                            {{ $user->initials() }}
                        </span>
                    </div>
                @endif
                {{-- Indicador de estado online --}}
                <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-zinc-50 dark:border-zinc-900 {{ $user->activo ? 'bg-red-500' : 'bg-zinc-400' }}"></span>
            </div>

            {{-- Controlos da foto --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3 truncate">{{ $user->email }}</p>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        Alterar foto
                        <input type="file" wire:model="foto" accept="image/jpeg,image/png,image/webp" class="sr-only" />
                    </label>

                    @if ($user->foto_perfil)
                        <button
                            wire:click="removerFoto"
                            wire:confirm="Tem a certeza que deseja remover a fotografia de perfil?"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/30 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/20 transition-colors"
                        >
                            Remover
                        </button>
                    @endif
                </div>

                @if ($foto)
                    <div class="mt-2 flex items-center gap-2" wire:loading.class.remove="flex" wire:loading.class="hidden">
                        <button
                            wire:click="uploadFoto"
                            type="button"
                            class="text-xs px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium"
                        >
                            Guardar foto
                        </button>
                        <button wire:click="$set('foto', null)" type="button" class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">
                            Cancelar
                        </button>
                    </div>
                    <div wire:loading wire:target="foto" class="mt-2 text-xs text-zinc-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        A carregar…
                    </div>
                @endif

                @error('foto')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror

                <p class="mt-2 text-xs text-zinc-400 dark:text-zinc-500">JPG, PNG ou WebP · Máx. 2 MB</p>
            </div>
        </div>

        {{-- ── Formulário de informação pessoal ───────────── --}}
        <form wire:submit="updateProfileInformation" class="space-y-5">
            <flux:input
                wire:model="name"
                label="Nome completo"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="O seu nome completo"
            />

            <div>
                <flux:input
                    wire:model="email"
                    label="Email institucional"
                    type="email"
                    required
                    autocomplete="email"
                />

                @if ($hasUnverifiedEmail)
                    <div class="mt-2 flex flex-col gap-1 p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30 rounded-lg">
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            O endereço de email ainda não está verificado.
                            <button wire:click.prevent="resendVerificationNotification" class="underline font-medium cursor-pointer">
                                Reenviar email de verificação.
                            </button>
                        </p>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                            Email de verificação enviado.
                        </p>
                    @endif
                @endif
            </div>

            <div class="flex items-center justify-between pt-1">
                <flux:button type="submit" variant="primary" icon="check">
                    Guardar alterações
                </flux:button>

                <x-action-message on="profile-updated" class="text-sm text-red-600 dark:text-red-400 font-medium">
                    Guardado.
                </x-action-message>
            </div>
        </form>

        {{-- ── Cargo e Jurisdição ──────────────────────────── --}}
        <div class="mt-8">
            <div class="flex items-center gap-2 mb-3">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Cargo e Jurisdição</h3>
                <span class="text-xs text-zinc-400 dark:text-zinc-500">(gerido pelo administrador)</span>
            </div>

            <div class="space-y-2.5">
                {{-- Papel --}}
                <div class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center
                            @if($user->nivelMinimo() === 1) bg-red-100 dark:bg-red-900/30
                            @elseif($user->nivelMinimo() === 2) bg-blue-100 dark:bg-blue-900/30
                            @elseif($user->nivelMinimo() === 3) bg-violet-100 dark:bg-violet-900/30
                            @else bg-amber-100 dark:bg-amber-900/30 @endif">
                            <svg class="w-4 h-4
                                @if($user->nivelMinimo() === 1) text-red-600 dark:text-red-400
                                @elseif($user->nivelMinimo() === 2) text-blue-600 dark:text-blue-400
                                @elseif($user->nivelMinimo() === 3) text-violet-600 dark:text-violet-400
                                @else text-amber-600 dark:text-amber-400 @endif"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Cargo / Perfil</p>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $user->roles->pluck('descricao')->implode(', ') ?: '—' }}
                            </p>
                        </div>
                    </div>
                    @if($user->roles->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            @foreach($user->roles->pluck('grupo')->unique() as $grupo)
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                                    @if($user->nivelMinimo() === 1) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                    @elseif($user->nivelMinimo() === 2) bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($user->nivelMinimo() === 3) bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                    {{ $grupo }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Província --}}
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
                    <div class="w-9 h-9 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Província</p>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $user->provincia?->nome ?? 'Todas as províncias' }}
                        </p>
                    </div>
                </div>

                {{-- Município --}}
                @if($user->municipio)
                    <div class="flex items-center gap-3 p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
                        <div class="w-9 h-9 rounded-lg bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Município</p>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $user->municipio->nome }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Informação da Conta ──────────────────────────── --}}
        <div class="mt-6 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
            <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-3">Informação da Conta</p>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800 space-y-0">
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">ID de utilizador</span>
                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded">
                        #{{ $user->id }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Membro desde</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">
                        {{ $user->created_at?->translatedFormat('d \d\e F \d\e Y') }}
                    </span>
                </div>
                @if($user->criadoPor)
                    <div class="flex justify-between items-center py-2 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">Criado por</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $user->criadoPor->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Estado da conta</span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->activo ? 'text-red-600 dark:text-red-400' : 'text-red-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $user->activo ? 'bg-red-500' : 'bg-red-500' }}"></span>
                        {{ $user->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Eliminar Conta ──────────────────────────────── --}}
        @unless(auth()->user()->isProvincial())
            <livewire:pages::settings.delete-user-form />
        @endunless
    </x-pages::settings.layout>
</div>
