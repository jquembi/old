<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Aparência')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout
        heading="Aparência"
        subheading="Personalize a aparência visual do sistema"
    >
        {{-- ── Tema visual ─────────────────────────────────── --}}
        <div class="space-y-3">
            <div>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tema visual</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                    Escolha entre o modo claro, escuro ou automático (segue o sistema operativo).
                </p>
            </div>

            <flux:radio.group
                x-data
                variant="segmented"
                x-model="$flux.appearance"
                class="w-full"
            >
                <flux:radio value="light" icon="sun">Claro</flux:radio>
                <flux:radio value="dark" icon="moon">Escuro</flux:radio>
                <flux:radio value="system" icon="computer-desktop">Sistema</flux:radio>
            </flux:radio.group>
        </div>

        <flux:separator variant="subtle" class="my-6" />

        {{-- ── Pré-visualizações ─────────────────────────── --}}
        <div class="grid grid-cols-3 gap-3">
            {{-- Claro --}}
            <button
                x-data
                @click="$flux.appearance = 'light'"
                type="button"
                class="group relative overflow-hidden rounded-xl border-2 transition-all"
                :class="$flux.appearance === 'light' ? 'border-red-500 shadow-md shadow-red-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
            >
                <div class="bg-white p-3 aspect-video flex flex-col gap-1.5">
                    <div class="flex gap-1">
                        <div class="h-1.5 w-8 rounded-full bg-zinc-200"></div>
                        <div class="h-1.5 w-5 rounded-full bg-zinc-100"></div>
                    </div>
                    <div class="flex gap-1 flex-1">
                        <div class="w-6 bg-zinc-100 rounded"></div>
                        <div class="flex-1 bg-zinc-50 rounded space-y-1 p-1">
                            <div class="h-1 w-full bg-zinc-200 rounded-full"></div>
                            <div class="h-1 w-3/4 bg-zinc-200 rounded-full"></div>
                            <div class="h-1 w-1/2 bg-red-200 rounded-full"></div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-center py-1.5 font-medium text-zinc-600 bg-zinc-50">Claro</p>
                <div
                    x-show="$flux.appearance === 'light'"
                    class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-red-500 flex items-center justify-center"
                >
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>

            {{-- Escuro --}}
            <button
                x-data
                @click="$flux.appearance = 'dark'"
                type="button"
                class="group relative overflow-hidden rounded-xl border-2 transition-all"
                :class="$flux.appearance === 'dark' ? 'border-red-500 shadow-md shadow-red-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
            >
                <div class="bg-zinc-900 p-3 aspect-video flex flex-col gap-1.5">
                    <div class="flex gap-1">
                        <div class="h-1.5 w-8 rounded-full bg-zinc-700"></div>
                        <div class="h-1.5 w-5 rounded-full bg-zinc-800"></div>
                    </div>
                    <div class="flex gap-1 flex-1">
                        <div class="w-6 bg-zinc-800 rounded"></div>
                        <div class="flex-1 bg-zinc-950 rounded space-y-1 p-1">
                            <div class="h-1 w-full bg-zinc-700 rounded-full"></div>
                            <div class="h-1 w-3/4 bg-zinc-700 rounded-full"></div>
                            <div class="h-1 w-1/2 bg-red-800 rounded-full"></div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-center py-1.5 font-medium text-zinc-400 bg-zinc-900">Escuro</p>
                <div
                    x-show="$flux.appearance === 'dark'"
                    class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-red-500 flex items-center justify-center"
                >
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>

            {{-- Sistema --}}
            <button
                x-data
                @click="$flux.appearance = 'system'"
                type="button"
                class="group relative overflow-hidden rounded-xl border-2 transition-all"
                :class="$flux.appearance === 'system' ? 'border-red-500 shadow-md shadow-red-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
            >
                <div class="aspect-video flex overflow-hidden flex-col gap-1.5">
                    <div class="flex-1 grid grid-cols-2">
                        <div class="bg-white p-1.5 flex flex-col gap-1">
                            <div class="h-1 w-full bg-zinc-200 rounded-full"></div>
                            <div class="h-1 w-2/3 bg-red-200 rounded-full"></div>
                        </div>
                        <div class="bg-zinc-900 p-1.5 flex flex-col gap-1">
                            <div class="h-1 w-full bg-zinc-700 rounded-full"></div>
                            <div class="h-1 w-2/3 bg-red-800 rounded-full"></div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-center py-1.5 font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900">Auto</p>
                <div
                    x-show="$flux.appearance === 'system'"
                    class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-red-500 flex items-center justify-center"
                >
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </div>

        <flux:separator variant="subtle" class="my-6" />

        {{-- ── Informação da plataforma ──────────────────── --}}
        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800">
            <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-3">Sobre o G.TERRAS</p>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Versão</span>
                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded">
                        Laravel {{ app()->version() }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Ambiente</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ app()->isProduction() ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                        {{ app()->isProduction() ? 'Produção' : ucfirst(app()->environment()) }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Timezone</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300 text-xs">{{ config('app.timezone') }}</span>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
