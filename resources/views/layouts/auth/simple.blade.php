<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
    <head>
        @include('partials.head')
        <style>
            .auth-hero { position: relative; overflow: hidden; }
            .auth-slide {
                position: absolute; inset: 0;
                display: flex; flex-direction: column; justify-content: space-between;
                padding: 2.5rem;
                opacity: 0; visibility: hidden;
                transition: opacity .6s ease, visibility .6s ease;
            }
            .auth-slide.is-active { opacity: 1; visibility: visible; }
            .auth-dots { display: flex; gap: .4rem; }
            .auth-dot { width: .5rem; height: .5rem; border-radius: 999px; border: 0; background: rgba(255,255,255,.35); cursor: pointer; padding: 0; }
            .auth-dot.is-active { width: 1.6rem; background: #F2CB13; }
            @media (prefers-reduced-motion: reduce) {
                .auth-slide { transition: none; }
            }
        </style>
    </head>
    <body class="theme-glass min-h-screen antialiased">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,.95fr)]">
            <aside class="auth-hero relative hidden min-h-screen text-white lg:block" id="authHero">
                <div class="absolute inset-0" style="background: radial-gradient(700px 420px at 0% 100%, rgba(195,14,32,.4), transparent 55%), linear-gradient(160deg, #07080d, #1a060c 70%);"></div>

                <article class="auth-slide is-active">
                    <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
                        <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-12 w-auto rounded-full bg-white p-1.5" />
                        <span>
                            <span class="block text-lg font-semibold">G.TERRAS</span>
                            <span class="block text-sm text-white/65">Gestão territorial da Huíla</span>
                        </span>
                    </a>
                    <div class="relative z-10 max-w-lg">
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-[#F2CB13]">INOTU</p>
                        <h2 class="mt-3 text-4xl font-bold leading-tight">A base provincial de gestão de terras</h2>
                        <p class="mt-4 text-base leading-7 text-white/75">Processos fundiários, loteamentos e operações urbanísticas com o âmbito da sua conta: nacional, provincial ou municipal.</p>
                    </div>
                    <p class="relative z-10 text-sm text-white/50">República de Angola · Província da Huíla</p>
                </article>

                <article class="auth-slide">
                    <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
                        <img src="/imagens/no-background/MINOPUH.png" alt="MINOPUH" class="h-12 w-auto rounded-xl bg-white p-1.5" />
                        <span class="text-lg font-semibold">Área institucional</span>
                    </a>
                    <div class="relative z-10 max-w-lg">
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-[#F2CB13]">Acesso</p>
                        <h2 class="mt-3 text-4xl font-bold leading-tight">Credenciais com o território certo</h2>
                        <p class="mt-4 text-base leading-7 text-white/75">Cada sessão abre apenas os municípios e módulos autorizados. A auditoria regista a entrada e as acções seguintes.</p>
                    </div>
                    <p class="relative z-10 text-sm text-white/50">MINOPUH · Urbanismo e habitação</p>
                </article>

                <article class="auth-slide">
                    <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
                        <img src="/imagens/no-background/SONA.png" alt="Projecto SONA" class="h-12 w-auto rounded-xl bg-white p-1.5" />
                        <span class="text-lg font-semibold">Projecto SONA</span>
                    </a>
                    <div class="relative z-10 max-w-lg">
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-[#F2CB13]">Utentes</p>
                        <h2 class="mt-3 text-4xl font-bold leading-tight">A consulta pública fica no portal</h2>
                        <p class="mt-4 text-base leading-7 text-white/75">Quem não tem conta institucional acompanha solicitações pelo BI, RUPE ou referência, sem entrar nesta área.</p>
                        <a href="{{ route('portal.home') }}" class="mt-6 inline-flex rounded-lg border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur hover:bg-white/15">Abrir portal do utente</a>
                    </div>
                    <p class="relative z-10 text-sm text-white/50">Requalificação e reconversão urbana</p>
                </article>

                <div class="absolute bottom-8 left-10 z-20 flex items-center gap-3">
                    <div class="auth-dots" id="authDots">
                        <button type="button" class="auth-dot is-active" data-auth-go="0" aria-label="Gestão de terras"></button>
                        <button type="button" class="auth-dot" data-auth-go="1" aria-label="Acesso institucional"></button>
                        <button type="button" class="auth-dot" data-auth-go="2" aria-label="Portal do utente"></button>
                    </div>
                </div>
            </aside>

            <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-8">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#C30E20] via-black to-[#C30E20] lg:hidden"></div>
                <div class="w-full max-w-md">
                    <div class="mb-5 flex items-center justify-between gap-3 lg:hidden">
                        <a href="{{ route('home') }}" class="flex items-center gap-2">
                            <img src="/imagens/no-background/INOTU.png" alt="INOTU" class="h-11 w-auto rounded-full bg-white p-1" />
                            <span class="text-sm font-bold text-zinc-900 dark:text-white">G.TERRAS</span>
                        </a>
                        <a href="{{ route('portal.home') }}" class="text-xs font-semibold text-[#C30E20]">Portal do utente</a>
                    </div>

                    <div class="rounded-3xl border border-white/70 bg-white/80 p-6 shadow-2xl backdrop-blur-2xl sm:p-8 dark:border-white/10 dark:bg-zinc-950/70">
                        <p class="mb-5 hidden text-xs font-semibold text-zinc-500 lg:block">
                            <a href="{{ route('home') }}" class="hover:text-[#C30E20]">Página inicial</a>
                            <span class="px-1.5 text-zinc-300">/</span>
                            Acesso institucional
                        </p>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <x-toast-container />
        @fluxScripts
        <script>
            (function () {
                var root = document.getElementById('authHero');
                if (!root) return;
                var slides = root.querySelectorAll('.auth-slide');
                var dots = root.querySelectorAll('.auth-dot');
                var current = 0;
                var timer;
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function goTo(index) {
                    current = (index + slides.length) % slides.length;
                    slides.forEach(function (slide, idx) { slide.classList.toggle('is-active', idx === current); });
                    dots.forEach(function (dot, idx) { dot.classList.toggle('is-active', idx === current); });
                    clearInterval(timer);
                    if (!reduce) timer = setInterval(function () { goTo(current + 1); }, 6500);
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () { goTo(Number(dot.getAttribute('data-auth-go'))); });
                });
                goTo(0);
            })();
        </script>
    </body>
</html>
