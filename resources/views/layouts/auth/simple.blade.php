<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="relative grid min-h-screen flex-col items-center justify-center lg:max-w-none lg:grid-cols-2">
            {{-- Painel Institucional --}}
            <div class="relative hidden h-full flex-col overflow-hidden lg:flex">
                {{-- Fundo com gradiente institucional --}}
                <div class="absolute inset-0 bg-gradient-to-br from-zinc-950 via-red-950 to-zinc-950" style="background: linear-gradient(135deg, #0a0a0a 0%, #1a0005 40%, #0f0000 100%);"></div>

                {{-- Padrão decorativo --}}
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-white/10 rounded-full translate-y-1/3 -translate-x-1/3"></div>
                </div>

                {{-- Conteúdo institucional --}}
                <div class="relative z-10 flex flex-col justify-between h-full p-10 text-white">
                    {{-- Logo e nome do sistema --}}
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-12 w-auto bg-white rounded-full p-1.5 drop-shadow-lg" />
                            <div>
                                <h2 class="text-xl font-semibold tracking-tight">G.TERRAS</h2>
                                <p class="text-sm text-red-200/80">Sistema de Gestão Territorial</p>
                            </div>
                        </div>
                    </div>

                    {{-- Descrição central --}}
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-3xl font-semibold leading-tight tracking-tight">
                                Base de Dados Provincial de Gestão de Terras
                            </h1>
                            <p class="mt-4 text-base text-white/75 leading-relaxed">
                                Plataforma digital do Instituto Nacional de Ordenamento do Território e Urbanismo para a gestão integrada de processos fundiários da Província da Huíla.
                            </p>
                        </div>

                        {{-- Funcionalidades --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-white/85">Registo e acompanhamento de processos fundiários</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-white/85">Gestão territorial por província e município</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-white/85">Relatórios e auditoria de actividades</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rodapé institucional --}}
                    <div class="pt-6 border-t border-white/15">
                        <div class="flex items-center gap-4 mb-3">
                            <img src="/imagens/no-background/INSIGNIA.png" alt="República de Angola" class="h-8 w-auto bg-white rounded-full p-1 opacity-70" />
                            <img src="/imagens/no-background/SONA.png" alt="Projecto SONA" class="h-8 w-auto bg-white rounded-full p-1 opacity-70" />
                        </div>
                        <p class="text-xs text-white/55 leading-relaxed">
                            INOTU — Instituto Nacional de Ordenamento do Território e Urbanismo
                        </p>
                        <p class="text-xs text-white/35 mt-1">
                            República de Angola &bull; Província da Huíla
                        </p>
                    </div>
                </div>
            </div>

            {{-- Painel do formulário --}}
            <div class="flex items-center justify-center p-6 md:p-10 bg-white dark:bg-zinc-900 min-h-screen relative">
                {{-- Linha de acento vermelha no topo (mobile) --}}
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-red-700 via-red-600 to-red-700 lg:hidden"></div>

                <div class="w-full max-w-sm space-y-6">
                    {{-- Logo para mobile --}}
                    <div class="flex flex-col items-center gap-3 lg:hidden mb-4">
                        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-14 w-auto bg-white rounded-full p-1.5 shadow-md" />
                        <div class="text-center">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">G.TERRAS</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Sistema de Gestão Territorial</p>
                        </div>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>

        {{-- Toast global (inclui suporte a session flash) --}}
        <x-toast-container />

        @fluxScripts
    </body>
</html>
