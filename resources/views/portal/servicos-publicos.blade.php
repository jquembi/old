<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Serviços Públicos'])
</head>
<body class="min-h-screen bg-zinc-950 text-white antialiased">
    <main class="min-h-screen">
        <section class="border-b border-white/10">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('imagens/no-background/INOTU.png') }}" alt="INOTU" class="h-10 w-auto rounded bg-white p-1">
                        <span class="text-sm font-semibold tracking-wide">Portal do Utente</span>
                    </a>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('portal.consulta') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Consultar processo</a>
                        <a href="{{ route('home') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Home</a>
                    </div>
                </div>

                <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-red-300">Transparência institucional</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-5xl">Serviços públicos do INOTU</h1>
                        <p class="mt-4 text-base leading-7 text-zinc-300">
                            Consulte processos, pareceres e operações que foram marcados como públicos no sistema institucional.
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 rounded-lg border border-white/10 bg-white/5 p-3">
                        <div class="rounded-md bg-black/20 p-3">
                            <p class="text-xs text-zinc-400">Croquis</p>
                            <p id="statCroquis" class="mt-1 text-xl font-bold">-</p>
                        </div>
                        <div class="rounded-md bg-black/20 p-3">
                            <p class="text-xs text-zinc-400">Pareceres</p>
                            <p id="statPareceres" class="mt-1 text-xl font-bold">-</p>
                        </div>
                        <div class="rounded-md bg-black/20 p-3">
                            <p class="text-xs text-zinc-400">Operações</p>
                            <p id="statOperacoes" class="mt-1 text-xl font-bold">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-end">
                <div>
                    <h2 class="text-lg font-semibold">Registos públicos</h2>
                    <p id="mensagem" class="mt-1 text-sm text-zinc-400">A carregar serviços públicos...</p>
                </div>
                <div class="grid gap-2 sm:grid-cols-3">
                    <select id="filtroTipo" class="rounded-md border border-white/10 bg-zinc-900 px-3 py-2 text-sm text-white">
                        <option value="">Todos</option>
                        <option value="croqui">Croquis</option>
                        <option value="terreno_ocioso">Terrenos</option>
                        <option value="parecer">Pareceres</option>
                        <option value="operacao">Operações</option>
                    </select>
                    <input id="filtroTexto" type="search" placeholder="Pesquisar..." class="rounded-md border border-white/10 bg-zinc-900 px-3 py-2 text-sm text-white placeholder:text-zinc-500">
                    <button id="recarregar" type="button" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-red-50">Recarregar</button>
                </div>
            </div>

            <div id="loading" class="rounded-lg border border-blue-400/30 bg-blue-500/10 p-4 text-sm text-blue-100">A consultar...</div>
            <div id="resultados" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
        </section>
    </main>

    <script>
        const resultados = document.getElementById('resultados');
        const mensagem = document.getElementById('mensagem');
        const loading = document.getElementById('loading');
        const apiConsultaUrl = @json(config('portal.api_base_url').'/api/v1/consulta');
        let dadosResultados = [];
        const cores = {
            green: 'border-emerald-400/30 bg-emerald-500/10 text-emerald-100',
            blue: 'border-blue-400/30 bg-blue-500/10 text-blue-100',
            yellow: 'border-amber-400/30 bg-amber-500/10 text-amber-100',
            amber: 'border-amber-400/30 bg-amber-500/10 text-amber-100',
            red: 'border-red-400/30 bg-red-500/10 text-red-100',
            violet: 'border-violet-400/30 bg-violet-500/10 text-violet-100',
            sky: 'border-sky-400/30 bg-sky-500/10 text-sky-100',
        };

        document.getElementById('recarregar').addEventListener('click', carregar);
        document.getElementById('filtroTipo').addEventListener('input', () => renderizar(dadosResultados));
        document.getElementById('filtroTexto').addEventListener('input', () => renderizar(dadosResultados));
        carregar();

        async function carregar() {
            resultados.innerHTML = '';
            loading.classList.remove('hidden');
            mensagem.textContent = 'A carregar serviços públicos...';

            try {
                const resposta = await fetch(`${apiConsultaUrl}?publicos=1`, { headers: { 'Accept': 'application/json' } });
                const json = await resposta.json();
                dadosResultados = json.resultados || [];
                actualizarStats(json.resumo);
                mensagem.textContent = json.mensagem || 'Consulta concluída.';
                renderizar(dadosResultados);
            } catch (erro) {
                mensagem.textContent = 'Não foi possível carregar os serviços públicos.';
                resultados.innerHTML = '<div class="rounded-lg border border-red-400/30 bg-red-500/10 p-5 text-sm text-red-100 md:col-span-2 xl:col-span-3">Erro ao comunicar com a API.</div>';
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderizar(items) {
            const tipo = document.getElementById('filtroTipo').value;
            const texto = document.getElementById('filtroTexto').value.toLowerCase();
            const filtrados = items.filter((item) => {
                const combinaTipo = !tipo || item.tipo === tipo;
                const corpo = `${item.titulo || ''} ${item.municipio || ''} ${item.referencia || ''} ${item.servico || ''}`.toLowerCase();
                return combinaTipo && (!texto || corpo.includes(texto));
            });

            if (!filtrados.length) {
                resultados.innerHTML = '<div class="rounded-lg border border-white/10 bg-zinc-900 p-5 text-sm text-zinc-300 md:col-span-2 xl:col-span-3">Ainda não existem serviços públicos para os filtros actuais.</div>';
                return;
            }

            resultados.innerHTML = filtrados.map((item) => {
                const cor = cores[item.estado?.cor] || cores.blue;
                return `
                    <article class="rounded-lg border border-white/10 bg-zinc-900 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">${esc(item.servico || item.tipo)}</p>
                                <h3 class="mt-1 text-lg font-semibold">${esc(item.titulo || 'Serviço público')}</h3>
                                <p class="mt-1 text-sm text-zinc-400">${esc(item.municipio || 'Município não informado')} · ${esc(item.referencia || '')}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs font-semibold ${cor}">${esc(item.estado?.rotulo || 'Estado')}</span>
                        </div>
                        <p class="mt-4 text-sm text-zinc-300">${esc(item.estado?.descricao || '')}</p>
                        <div class="mt-4 rounded-md bg-white/5 p-3 text-sm text-zinc-200">
                            <span class="font-semibold">Próximo passo:</span> ${esc(item.proximo_passo || 'Aguarde actualização.')}
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            ${item.verificacao_url ? `<a href="${esc(item.verificacao_url)}" target="_blank" class="rounded-md border border-emerald-400/30 px-3 py-2 text-xs font-semibold text-emerald-100 hover:bg-emerald-500/10">Verificar documento</a>` : ''}
                            <a href="{{ route('portal.consulta') }}?referencia=${encodeURIComponent(item.referencia || '')}" class="rounded-md bg-white px-3 py-2 text-xs font-semibold text-zinc-950 hover:bg-red-50">Abrir consulta</a>
                        </div>
                    </article>`;
            }).join('');
        }

        function actualizarStats(resumo) {
            document.getElementById('statCroquis').textContent = resumo?.servicos_publicos?.croquis ?? '-';
            document.getElementById('statPareceres').textContent = resumo?.servicos_publicos?.pareceres ?? '-';
            document.getElementById('statOperacoes').textContent = resumo?.servicos_publicos?.operacoes ?? '-';
        }

        function esc(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, (s) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[s]));
        }
    </script>
</body>
</html>
