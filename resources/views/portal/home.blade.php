<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Portal do Utente'])
</head>
<body class="min-h-screen bg-zinc-950 text-white antialiased">
    <main>
        <section class="border-b border-white/10 bg-zinc-950">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('imagens/no-background/INOTU.png') }}" alt="INOTU" class="h-11 w-auto rounded bg-white p-1">
                        <div>
                            <p class="text-sm font-semibold">G.TERRAS</p>
                            <p class="text-xs text-zinc-400">Portal do Utente</p>
                        </div>
                    </a>
                    <nav class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('portal.consulta') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-red-50">Consultar solicitação</a>
                        <a href="{{ route('portal.servicos-publicos') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Serviços públicos</a>
                        <a href="{{ route('login') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Área institucional</a>
                    </nav>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(220,38,38,0.22),transparent_35%),linear-gradient(135deg,#09090b,#18181b)]"></div>
            <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[minmax(0,1fr)_420px] lg:px-8 lg:py-24">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-red-300">Atendimento digital</p>
                    <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-6xl">Portal público de consulta territorial</h1>
                    <p class="mt-5 text-lg leading-8 text-zinc-300">
                        Acompanhe solicitações pelo Bilhete de Identidade, consulte referências de pagamento e veja serviços do INOTU disponibilizados ao público.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('portal.consulta') }}" class="rounded-md bg-red-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-800">Consultar pelo BI</a>
                        <a href="{{ route('portal.servicos-publicos') }}" class="rounded-md bg-white px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-red-50">Ver serviços públicos</a>
                    </div>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/10 p-5 shadow-2xl backdrop-blur">
                    <h2 class="text-lg font-semibold">O que pode consultar</h2>
                    <div class="mt-5 grid gap-3">
                        <div class="rounded-md border border-white/10 bg-black/20 p-4">
                            <p class="font-semibold">Solicitações privadas</p>
                            <p class="mt-1 text-sm text-zinc-300">Croquis e terrenos associados ao número do BI do proprietário registado.</p>
                        </div>
                        <div class="rounded-md border border-white/10 bg-black/20 p-4">
                            <p class="font-semibold">Pagamentos e RUPE</p>
                            <p class="mt-1 text-sm text-zinc-300">Estado de referência, valor, prazo e próximo passo para o utente.</p>
                        </div>
                        <div class="rounded-md border border-white/10 bg-black/20 p-4">
                            <p class="font-semibold">Serviços públicos</p>
                            <p class="mt-1 text-sm text-zinc-300">Processos marcados pelo INOTU como públicos nos formulários internos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white py-12 text-zinc-950">
            <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
                <div class="rounded-lg border border-zinc-200 p-5">
                    <p class="text-sm font-semibold text-red-700">1. Informe os dados</p>
                    <p class="mt-2 text-sm text-zinc-600">Use o BI, uma referência RUPE, token QR ou referência do processo.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 p-5">
                    <p class="text-sm font-semibold text-red-700">2. Veja o estado</p>
                    <p class="mt-2 text-sm text-zinc-600">O portal mostra se está pendente, em análise, pago, homologado ou concluído.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 p-5">
                    <p class="text-sm font-semibold text-red-700">3. Siga o próximo passo</p>
                    <p class="mt-2 text-sm text-zinc-600">Cada resultado apresenta a orientação necessária para continuar o processo.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
