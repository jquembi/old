<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Consulta de Solicitações'])
</head>
<body class="min-h-screen bg-zinc-950 text-white antialiased">
    <main class="min-h-screen">
        <section class="border-b border-white/10 bg-zinc-950">
            <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('imagens/no-background/INOTU.png') }}" alt="INOTU" class="h-10 w-auto rounded bg-white p-1">
                        <span class="text-sm font-semibold tracking-wide">Portal do Utente</span>
                    </a>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('portal.servicos-publicos') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Serviços públicos</a>
                        <a href="{{ route('login') }}" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Área institucional</a>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-red-300">Consulta integrada</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-5xl">Acompanhe solicitações, pagamentos e pareceres</h1>
                        <p class="mt-4 text-base leading-7 text-zinc-300">
                            Consulte processos pelo BI, referência RUPE, token QR ou acompanhe serviços marcados como públicos pelo INOTU.
                        </p>
                    </div>
                    <div id="stats" class="grid grid-cols-3 gap-2 rounded-lg border border-white/10 bg-white/5 p-3">
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

        <section class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[390px_minmax(0,1fr)] lg:px-8">
            <aside class="space-y-4">
                <form id="consultaForm" class="rounded-lg border border-white/10 bg-white p-5 text-zinc-900 shadow-xl">
                    <h2 class="text-lg font-semibold">Consultar processo</h2>
                    <p class="mt-1 text-sm text-zinc-500">Use BI, referência ou liste os serviços públicos.</p>

                    <label class="mt-5 block text-sm font-medium text-zinc-700" for="bi">Bilhete de Identidade</label>
                    <input id="bi" name="bi" type="text" placeholder="Ex: 000000000LA000" autocomplete="off"
                        class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm uppercase outline-none ring-red-600 transition focus:ring-2">

                    <label class="mt-4 block text-sm font-medium text-zinc-700" for="referencia">Referência, RUPE ou token QR</label>
                    <input id="referencia" name="referencia" type="text" placeholder="Ex: CR-12, PA-8, RUPE..."
                        class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none ring-red-600 transition focus:ring-2">

                    <label class="mt-4 flex items-center gap-2 text-sm text-zinc-700">
                        <input id="publicos" name="publicos" type="checkbox" value="1" class="rounded border-zinc-300 text-red-600 focus:ring-red-600">
                        Incluir serviços públicos
                    </label>

                    <button type="submit" class="mt-5 w-full rounded-md bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800">
                        Consultar
                    </button>
                    <button id="limpar" type="button" class="mt-2 w-full rounded-md border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">
                        Limpar pesquisa
                    </button>
                </form>

                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <h3 class="text-sm font-semibold">Filtros rápidos</h3>
                    <div class="mt-3 grid gap-3">
                        <select id="filtroTipo" class="rounded-md border border-white/10 bg-zinc-900 px-3 py-2 text-sm text-white">
                            <option value="">Todos os tipos</option>
                            <option value="croqui">Croquis</option>
                            <option value="terreno_ocioso">Terrenos ociosos</option>
                            <option value="parecer">Pareceres</option>
                            <option value="pagamento">Pagamentos</option>
                            <option value="operacao">Operações</option>
                        </select>
                        <select id="filtroEstado" class="rounded-md border border-white/10 bg-zinc-900 px-3 py-2 text-sm text-white">
                            <option value="">Todos os estados</option>
                            <option value="em_analise">Em análise</option>
                            <option value="pagamento_pendente">Pagamento pendente</option>
                            <option value="homologacao_pendente">Homologação pendente</option>
                            <option value="concluido">Concluído</option>
                            <option value="rejeitado">Rejeitado</option>
                        </select>
                        <input id="filtroTexto" type="search" placeholder="Filtrar nos resultados..." class="rounded-md border border-white/10 bg-zinc-900 px-3 py-2 text-sm text-white placeholder:text-zinc-500">
                    </div>
                </div>
            </aside>

            <div class="rounded-lg border border-white/10 bg-white/5 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">Resultados</h2>
                        <p id="mensagem" class="mt-1 text-sm text-zinc-400">Use o formulário para iniciar a consulta.</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="btnPublicos" type="button" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Ver públicos</button>
                        <button id="btnImprimir" type="button" class="rounded-md border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:bg-white/10">Imprimir</button>
                    </div>
                </div>

                <div id="loading" class="mt-6 hidden rounded-lg border border-blue-400/30 bg-blue-500/10 p-4 text-sm text-blue-100">A consultar...</div>
                <div id="resultados" class="mt-6 grid gap-4"></div>
            </div>
        </section>
    </main>

    <div id="painelDetalhe" class="fixed inset-0 z-50 hidden bg-black/70 p-4">
        <div class="ml-auto flex h-full max-w-2xl flex-col rounded-lg border border-white/10 bg-zinc-950 shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5">
                <div>
                    <p id="detalheServico" class="text-xs font-semibold uppercase tracking-wide text-zinc-500"></p>
                    <h2 id="detalheTitulo" class="mt-1 text-xl font-bold"></h2>
                    <p id="detalheReferencia" class="mt-1 text-sm text-zinc-400"></p>
                </div>
                <button id="fecharDetalhe" class="rounded-md border border-white/15 px-3 py-2 text-sm font-semibold text-white/80 hover:bg-white/10">Fechar</button>
            </div>
            <div id="detalheConteudo" class="flex-1 overflow-y-auto p-5"></div>
        </div>
    </div>

    <script>
        const form = document.getElementById('consultaForm');
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

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            consultar(new FormData(form));
        });
        document.getElementById('btnPublicos').addEventListener('click', () => {
            const dados = new FormData();
            dados.set('publicos', '1');
            consultar(dados);
        });
        document.getElementById('btnImprimir').addEventListener('click', () => window.print());
        document.getElementById('limpar').addEventListener('click', () => {
            form.reset();
            dadosResultados = [];
            renderizar([]);
            mensagem.textContent = 'Use o formulário para iniciar a consulta.';
        });
        ['filtroTipo', 'filtroEstado', 'filtroTexto'].forEach((id) => {
            document.getElementById(id).addEventListener('input', () => renderizar(dadosResultados));
        });
        document.getElementById('fecharDetalhe').addEventListener('click', () => document.getElementById('painelDetalhe').classList.add('hidden'));

        const paramsIniciais = new URLSearchParams(window.location.search);
        if (paramsIniciais.has('bi') || paramsIniciais.has('referencia') || paramsIniciais.has('publicos')) {
            if (paramsIniciais.get('bi')) document.getElementById('bi').value = paramsIniciais.get('bi');
            if (paramsIniciais.get('referencia')) document.getElementById('referencia').value = paramsIniciais.get('referencia');
            if (paramsIniciais.get('publicos')) document.getElementById('publicos').checked = true;
            consultar(new FormData(form));
        }

        async function consultar(dados) {
            resultados.innerHTML = '';
            mensagem.textContent = 'A consultar os registos...';
            loading.classList.remove('hidden');

            const params = new URLSearchParams();
            for (const [chave, valor] of dados.entries()) {
                if (String(valor).trim() !== '') params.set(chave, valor);
            }
            history.replaceState(null, '', `${window.location.pathname}?${params.toString()}`);

            try {
                const resposta = await fetch(`${apiConsultaUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const json = await resposta.json();
                dadosResultados = json.resultados || [];
                actualizarStats(json.resumo);
                mensagem.textContent = json.mensagem || json.message || 'Consulta concluída.';
                renderizar(dadosResultados);
            } catch (erro) {
                mensagem.textContent = 'Não foi possível concluir a consulta.';
                resultados.innerHTML = cardErro();
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderizar(items) {
            const filtrados = filtrar(items);
            if (!filtrados.length) {
                resultados.innerHTML = cardVazio();
                return;
            }

            resultados.innerHTML = filtrados.map((item) => cardResultado(item)).join('');
            document.querySelectorAll('[data-detalhe]').forEach((botao) => {
                botao.addEventListener('click', () => abrirDetalhe(filtrados[Number(botao.dataset.detalhe)]));
            });
            document.querySelectorAll('[data-copiar]').forEach((botao) => {
                botao.addEventListener('click', async () => {
                    await navigator.clipboard?.writeText(botao.dataset.copiar);
                    botao.textContent = 'Copiado';
                    setTimeout(() => botao.textContent = 'Copiar referência', 1500);
                });
            });
        }

        function filtrar(items) {
            const tipo = document.getElementById('filtroTipo').value;
            const estado = document.getElementById('filtroEstado').value;
            const texto = document.getElementById('filtroTexto').value.toLowerCase();

            return items.filter((item) => {
                const combinaTipo = !tipo || item.tipo === tipo;
                const combinaEstado = !estado || item.estado?.codigo === estado;
                const textoItem = `${item.titulo || ''} ${item.municipio || ''} ${item.referencia || ''} ${item.servico || ''}`.toLowerCase();
                const combinaTexto = !texto || textoItem.includes(texto);
                return combinaTipo && combinaEstado && combinaTexto;
            });
        }

        function cardResultado(item) {
            const indice = filtrar(dadosResultados).findIndex((actual) => actual.tipo === item.tipo && actual.id === item.id);
            const cor = cores[item.estado?.cor] || cores.blue;
            const pagamento = item.pagamento ? `
                <div class="mt-4 rounded-md border border-white/10 bg-black/20 p-3 text-sm text-zinc-200">
                    <p class="font-semibold">Pagamento: ${esc(item.pagamento.rotulo || item.pagamento.estado || 'Não informado')}</p>
                    ${item.pagamento.referencia ? `<p class="mt-1 text-zinc-400">Referência: ${esc(item.pagamento.referencia)}</p>` : ''}
                    ${item.pagamento.valor_formatado ? `<p class="mt-1 text-zinc-400">Valor: ${esc(item.pagamento.valor_formatado)}</p>` : ''}
                </div>` : '';

            return `
                <article class="rounded-lg border border-white/10 bg-zinc-900 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">${esc(item.servico || item.tipo)}</p>
                            <h3 class="mt-1 text-lg font-semibold">${esc(item.titulo || 'Solicitação')}</h3>
                            <p class="mt-1 text-sm text-zinc-400">${esc(item.municipio || 'Município não informado')} · ${esc(item.referencia || '')}</p>
                        </div>
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold ${cor}">${esc(item.estado?.rotulo || 'Estado')}</span>
                    </div>
                    <p class="mt-4 text-sm text-zinc-300">${esc(item.estado?.descricao || '')}</p>
                    ${timeline(item.linha_tempo || [])}
                    ${pagamento}
                    <div class="mt-4 rounded-md bg-white/5 p-3 text-sm text-zinc-200">
                        <span class="font-semibold">Próximo passo:</span> ${esc(item.proximo_passo || 'Aguarde actualização.')}
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button data-detalhe="${indice}" class="rounded-md bg-white px-3 py-2 text-xs font-semibold text-zinc-950 hover:bg-red-50">Ver detalhes</button>
                        <button data-copiar="${esc(item.referencia || '')}" class="rounded-md border border-white/15 px-3 py-2 text-xs font-semibold text-white/80 hover:bg-white/10">Copiar referência</button>
                        ${item.verificacao_url ? `<a href="${esc(item.verificacao_url)}" target="_blank" class="rounded-md border border-emerald-400/30 px-3 py-2 text-xs font-semibold text-emerald-100 hover:bg-emerald-500/10">Verificar documento</a>` : ''}
                    </div>
                </article>`;
        }

        function timeline(passos) {
            if (!passos.length) return '';
            return `<div class="mt-4 grid gap-2 sm:grid-cols-5">${passos.map((passo) => {
                const classe = passo.estado === 'feito' ? 'bg-emerald-500/20 text-emerald-100 border-emerald-400/30'
                    : passo.estado === 'actual' ? 'bg-blue-500/20 text-blue-100 border-blue-400/30'
                    : passo.estado === 'alerta' ? 'bg-red-500/20 text-red-100 border-red-400/30'
                    : 'bg-white/5 text-zinc-400 border-white/10';
                return `<div class="rounded-md border px-2 py-2 text-center text-[11px] font-semibold ${classe}">${esc(passo.rotulo)}</div>`;
            }).join('')}</div>`;
        }

        function abrirDetalhe(item) {
            document.getElementById('detalheServico').textContent = item.servico || item.tipo || '';
            document.getElementById('detalheTitulo').textContent = item.titulo || 'Solicitação';
            document.getElementById('detalheReferencia').textContent = `${item.referencia || ''} · actualizado em ${item.actualizado_em_formatado || '-'}`;
            const detalhes = item.detalhes || {};
            document.getElementById('detalheConteudo').innerHTML = `
                ${timeline(item.linha_tempo || [])}
                <dl class="mt-5 grid gap-3">${Object.entries(detalhes).filter(([, valor]) => valor !== null && valor !== '').map(([chave, valor]) => `
                    <div class="rounded-md border border-white/10 bg-white/5 p-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">${esc(chave)}</dt>
                        <dd class="mt-1 text-sm text-zinc-100">${esc(valor)}</dd>
                    </div>`).join('')}</dl>
                <div class="mt-5 rounded-md bg-white/5 p-4 text-sm text-zinc-200">
                    <span class="font-semibold">Orientação:</span> ${esc(item.proximo_passo || 'Aguarde actualização.')}
                </div>`;
            document.getElementById('painelDetalhe').classList.remove('hidden');
        }

        function actualizarStats(resumo) {
            document.getElementById('statCroquis').textContent = resumo?.servicos_publicos?.croquis ?? '-';
            document.getElementById('statPareceres').textContent = resumo?.servicos_publicos?.pareceres ?? '-';
            document.getElementById('statOperacoes').textContent = resumo?.servicos_publicos?.operacoes ?? '-';
        }

        function cardVazio() {
            return '<div class="rounded-lg border border-white/10 bg-zinc-900 p-5 text-sm text-zinc-300">Nenhum registo encontrado para os critérios actuais.</div>';
        }

        function cardErro() {
            return '<div class="rounded-lg border border-red-400/30 bg-red-500/10 p-5 text-sm text-red-100">Erro ao comunicar com a API de consulta.</div>';
        }

        function esc(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, (s) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[s]));
        }
    </script>
</body>
</html>
