<!DOCTYPE html>
<html lang="pt" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Autenticado  G.TERRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        body { background: #09090b; font-family: 'Merriweather', serif; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-zinc-950 to-zinc-900 py-12 px-4">
<div class="max-w-2xl mx-auto">
    @php
        $estadoVerificacao = $estadoVerificacao ?? [
            'tipo' => 'autentico',
            'cor' => 'green',
            'titulo' => 'Documento Autêntico',
            'mensagem' => 'Este documento foi verificado e é original.',
            'rotulo' => 'Autêntico',
        ];

        $paletas = [
            'green' => [
                'banner' => 'from-green-900/30 to-emerald-800/20 border-green-700/40',
                'iconBox' => 'bg-green-500/10',
                'icon' => 'text-green-400',
                'title' => 'text-green-300',
                'text' => 'text-green-100/85',
                'dot' => 'bg-green-500',
                'badge' => 'bg-green-500/10 text-green-300 border-green-700/40',
                'button' => 'bg-green-600 hover:bg-green-700',
                'panel' => 'bg-green-900/20 border-green-700/30 text-green-200',
            ],
            'blue' => [
                'banner' => 'from-blue-900/30 to-sky-800/20 border-blue-700/40',
                'iconBox' => 'bg-blue-500/10',
                'icon' => 'text-blue-400',
                'title' => 'text-blue-300',
                'text' => 'text-blue-100/85',
                'dot' => 'bg-blue-500',
                'badge' => 'bg-blue-500/10 text-blue-300 border-blue-700/40',
                'button' => 'bg-blue-600 hover:bg-blue-700',
                'panel' => 'bg-blue-900/20 border-blue-700/30 text-blue-200',
            ],
            'yellow' => [
                'banner' => 'from-yellow-900/30 to-amber-800/20 border-yellow-700/40',
                'iconBox' => 'bg-yellow-500/10',
                'icon' => 'text-yellow-400',
                'title' => 'text-yellow-300',
                'text' => 'text-yellow-100/85',
                'dot' => 'bg-yellow-500',
                'badge' => 'bg-yellow-500/10 text-yellow-300 border-yellow-700/40',
                'button' => 'bg-yellow-600 hover:bg-yellow-700',
                'panel' => 'bg-yellow-900/20 border-yellow-700/30 text-yellow-200',
            ],
            'red' => [
                'banner' => 'from-red-900/30 to-red-800/20 border-red-700/40',
                'iconBox' => 'bg-red-500/10',
                'icon' => 'text-red-400',
                'title' => 'text-red-300',
                'text' => 'text-red-100/85',
                'dot' => 'bg-red-500',
                'badge' => 'bg-red-500/10 text-red-300 border-red-700/40',
                'button' => 'bg-red-600 hover:bg-red-700',
                'panel' => 'bg-red-900/20 border-red-700/30 text-red-200',
            ],
        ];

        $paleta = $paletas[$estadoVerificacao['cor'] ?? 'green'] ?? $paletas['green'];
    @endphp

    {{-- Marca --}}
    <div class="text-center mb-8 no-print">
        <p class="text-zinc-500 text-sm tracking-widest uppercase">G.TERRAS  Sistema de Gestão Territorial</p>
    </div>

    {{-- Cabeçalho de verificação --}}
    <div class="bg-gradient-to-r {{ $paleta['banner'] }} border rounded-lg p-8 mb-8">
        <div class="flex items-start gap-4">
            <div class="p-3 rounded-lg {{ $paleta['iconBox'] }}">
                @if(($estadoVerificacao['tipo'] ?? 'autentico') === 'pagamento_pendente')
                    <svg class="w-6 h-6 {{ $paleta['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75h19.5m-18-10.5h16.5m-15 0V6A2.25 2.25 0 0 1 7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v2.25m-13.5 0v10.5m13.5-10.5v10.5"/>
                    </svg>
                @elseif(($estadoVerificacao['tipo'] ?? 'autentico') === 'acao_necessaria')
                    <svg class="w-6 h-6 {{ $paleta['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 {{ $paleta['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <div class="mb-2 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $paleta['badge'] }}">
                    {{ $estadoVerificacao['rotulo'] }}
                </div>
                <h1 class="text-2xl font-bold {{ $paleta['title'] }} mb-1">{{ $estadoVerificacao['titulo'] }}</h1>
                <p class="{{ $paleta['text'] }}">{{ $estadoVerificacao['mensagem'] }}</p>
            </div>
        </div>
    </div>

    {{-- Cartão de detalhes --}}
    <div class="bg-zinc-900/80 border border-zinc-700/50 rounded-lg p-8 mb-8">
        <div class="grid gap-6">

            {{-- Tipo --}}
            <div class="border-b border-zinc-800 pb-6">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Tipo de Documento</p>
                <p class="text-lg font-semibold text-zinc-200">{{ $tipo }}</p>
            </div>

            {{-- Token --}}
            <div class="border-b border-zinc-800 pb-6">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Código de Autenticação</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <code class="flex-1 px-3 py-2 bg-zinc-800/50 rounded border border-zinc-700 text-sm text-zinc-300 font-mono break-all">{{ $ficheiro->qr_token }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ $ficheiro->qr_token }}'); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 2000);" class="no-print px-3 py-2 bg-zinc-800 hover:bg-zinc-700 rounded border border-zinc-700 text-xs font-medium text-zinc-300 transition-all">Copiar</button>
                </div>
            </div>

            @if($ficheiro->nome_original ?? null)
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Nome do Ficheiro</p>
                    <p class="text-zinc-300">{{ $ficheiro->nome_original }}</p>
                </div>
            @endif

            @if($ficheiro->tamanho ?? null)
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Tamanho</p>
                    <p class="text-zinc-300">{{ number_format($ficheiro->tamanho / 1024, 1) }} KB</p>
                </div>
            @endif

            @if($ficheiro->tipo_ficheiro ?? null)
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Tipo de Ficheiro</p>
                    <p class="text-zinc-300">{{ strtoupper($ficheiro->tipo_ficheiro) }}</p>
                </div>
            @endif

            {{-- Dados do Croqui: Proprietário + Localização --}}
            @if(!empty($dadosCroqui))
                @if($dadosCroqui['proprietarios'])
                    <div class="border-b border-zinc-800 pb-6">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Proprietário(s)</p>
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-amber-500/10">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                </svg>
                            </div>
                            <p class="text-zinc-200 font-medium">{{ $dadosCroqui['proprietarios'] }}</p>
                        </div>
                    </div>
                @endif

                @if($dadosCroqui['localizacao'])
                    <div class="border-b border-zinc-800 pb-6">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Localização do Croqui</p>
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-blue-500/10">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-zinc-200 font-mono text-sm">{{ $dadosCroqui['localizacao'] }}</p>
                                @if($dadosCroqui['municipio'])
                                    <p class="text-zinc-500 text-sm mt-1">Município: {{ $dadosCroqui['municipio'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($dadosCroqui['titulo'])
                    <div class="border-b border-zinc-800 pb-6">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Título do Croqui</p>
                        <p class="text-zinc-300">{{ $dadosCroqui['titulo'] }}</p>
                    </div>
                @endif

                @if($dadosCroqui['operacao'])
                    <div class="border-b border-zinc-800 pb-6">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Operação Urbanística</p>
                        <p class="text-zinc-300">{{ $dadosCroqui['operacao'] }}</p>
                    </div>
                @endif
            @endif

            @if(!empty($dadosParecer))
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">Parecer INOTU</p>
                    <div class="rounded-lg border border-red-900/40 bg-red-950/20 p-4 space-y-3">
                        <div>
                            <p class="text-zinc-100 font-semibold">{{ $dadosParecer['titulo'] }}</p>
                            <p class="text-sm text-zinc-400">{{ $dadosParecer['servico_label'] }} @if($dadosParecer['municipio']) · {{ $dadosParecer['municipio'] }} @endif</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 text-sm">
                            <div><span class="text-zinc-500">Área reservada:</span> <span class="text-zinc-200">{{ $dadosParecer['area_reservada_label'] }}</span></div>
                            <div><span class="text-zinc-500">Classificação:</span> <span class="text-zinc-200">{{ $dadosParecer['classificacao_area'] ?: 'Não informado' }}</span></div>
                            <div><span class="text-zinc-500">Uso do solo:</span> <span class="text-zinc-200">{{ $dadosParecer['uso_solo'] ?: 'Não informado' }}</span></div>
                            <div><span class="text-zinc-500">Zona:</span> <span class="text-zinc-200">{{ $dadosParecer['zona_ordenamento'] ?: 'Não informado' }}</span></div>
                        </div>
                        @if($dadosParecer['tipo_concessao'])
                            <p class="text-sm text-zinc-300"><span class="text-zinc-500">Tipo de concessão:</span> {{ $dadosParecer['tipo_concessao'] }}</p>
                        @endif
                        @if($dadosParecer['conclusao'])
                            <p class="text-sm text-zinc-300"><span class="text-zinc-500">Conclusão:</span> {{ $dadosParecer['conclusao'] }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(($pareceresAssociados ?? collect())->isNotEmpty())
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">Parecer(es) INOTU associado(s)</p>
                    <div class="space-y-3">
                        @foreach($pareceresAssociados as $parecer)
                            <div class="rounded-lg border border-red-900/40 bg-red-950/20 p-4 space-y-3">
                                <div>
                                    <p class="text-zinc-100 font-semibold">{{ $parecer['titulo'] }}</p>
                                    <p class="text-sm text-zinc-400">{{ $parecer['servico_label'] }} @if($parecer['municipio']) · {{ $parecer['municipio'] }} @endif</p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2 text-sm">
                                    <div><span class="text-zinc-500">Área reservada:</span> <span class="text-zinc-200">{{ $parecer['area_reservada_label'] }}</span></div>
                                    <div><span class="text-zinc-500">Classificação:</span> <span class="text-zinc-200">{{ $parecer['classificacao_area'] ?: 'Não informado' }}</span></div>
                                    <div><span class="text-zinc-500">Uso do solo:</span> <span class="text-zinc-200">{{ $parecer['uso_solo'] ?: 'Não informado' }}</span></div>
                                    <div><span class="text-zinc-500">Zona:</span> <span class="text-zinc-200">{{ $parecer['zona_ordenamento'] ?: 'Não informado' }}</span></div>
                                </div>
                                @if($parecer['conclusao'])
                                    <p class="text-sm text-zinc-300"><span class="text-zinc-500">Conclusão:</span> {{ $parecer['conclusao'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="border-b border-zinc-800 pb-6">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Data de Envio</p>
                <p class="text-zinc-300">{{ $ficheiro->created_at->format('d/m/Y H:i:s') }}</p>
            </div>

            @if($ficheiro->enviadoPor ?? null)
                <div class="border-b border-zinc-800 pb-6">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Enviado por</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-600 to-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ substr($ficheiro->enviadoPor->name, 0, 1) }}{{ substr(explode(' ', $ficheiro->enviadoPor->name)[1] ?? '', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-zinc-200">{{ $ficheiro->enviadoPor->name }}</p>
                            <p class="text-sm text-zinc-500">{{ $ficheiro->enviadoPor->email }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Status</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $paleta['dot'] }}"></div>
                    <span class="{{ $paleta['title'] }} font-medium">
                        {{ $estadoVerificacao['rotulo'] }}
                    </span>
                </div>
            </div>

            @if(!empty($detalhesDiretor))
                @php
                    $acoesDiretor = $detalhesDiretor['acoes_necessarias'] ?? [];
                    $metadadosDiretor = collect($detalhesDiretor)
                        ->except('acoes_necessarias')
                        ->filter(fn ($valor) => !is_array($valor) && $valor !== null && $valor !== '');
                @endphp

                <div class="border-t border-red-900/40 pt-6">
                    <p class="text-xs font-semibold text-red-300 uppercase tracking-wide mb-3">Painel do Diretor INOTU</p>

                    <div class="rounded-lg border border-yellow-700/40 bg-yellow-950/20 p-4 mb-4">
                        <p class="text-sm font-semibold text-yellow-200 mb-3">Ação necessária</p>
                        <ul class="space-y-2 text-sm text-yellow-100/90">
                            @foreach($acoesDiretor as $acao)
                                <li class="flex gap-2">
                                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-yellow-400 flex-shrink-0"></span>
                                    <span>{{ $acao }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if($metadadosDiretor->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2 text-sm">
                            @foreach($metadadosDiretor as $chave => $valor)
                                <div class="rounded border border-zinc-800 bg-zinc-950/40 p-3">
                                    <p class="text-xs uppercase tracking-wide text-zinc-500">{{ str_replace('_', ' ', $chave) }}</p>
                                    <p class="mt-1 break-all text-zinc-200">
                                        @if(is_bool($valor))
                                            {{ $valor ? 'Sim' : 'Não' }}
                                        @else
                                            {{ $valor }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    <div class="{{ $paleta['panel'] }} border rounded-lg p-6 mb-8">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 {{ $paleta['icon'] }} flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm leading-relaxed">
                Este código QR garante a autenticidade e integridade do documento. Foi gerado automaticamente no momento do envio e pode ser escaneado para verificação a qualquer momento.
            </p>
        </div>
    </div>

    @if(!empty($dados))
        <details class="no-print bg-zinc-900/50 border border-zinc-700/30 rounded-lg p-6 mb-8">
            <summary class="cursor-pointer text-sm font-semibold text-zinc-300 hover:text-zinc-100 transition-colors">Ver dados estruturados públicos (JSON)</summary>
            <pre class="mt-4 bg-zinc-950 rounded border border-zinc-700 p-4 overflow-auto text-xs text-zinc-400"><code>{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
        </details>
    @endif

    <div class="no-print flex gap-4">
        <a href="/" class="flex-1 px-6 py-3 bg-zinc-800 hover:bg-zinc-700 rounded-lg font-medium text-white transition-all text-center">
             Voltar ao Início
        </a>
        <button onclick="window.print()" class="flex-1 px-6 py-3 {{ $paleta['button'] }} rounded-lg font-medium text-white transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-2 0H8v4h8v-4z"/>
            </svg>
            Imprimir
        </button>
    </div>

</div>
</body>
</html>
