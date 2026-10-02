<!DOCTYPE html>
<html lang="pt" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Não Encontrado — G.TERRAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { background: #09090b; font-family: 'Merriweather', serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-zinc-950 to-zinc-900 py-12 px-4">
<div class="max-w-md mx-auto text-center">
    @php
        $estadoVerificacao = $estadoVerificacao ?? [
            'tipo' => 'nao_autentico',
            'cor' => 'red',
            'titulo' => 'Documento Não Autêntico',
            'mensagem' => $mensagem ?? 'O código QR fornecido não é válido ou o documento não existe no sistema.',
            'rotulo' => 'Não autêntico',
        ];
    @endphp

    <div class="text-center mb-8">
        <p class="text-zinc-500 text-sm tracking-widest uppercase">G.TERRAS — Sistema de Gestão Territorial</p>
    </div>

    {{-- Ícone de erro --}}
    <div class="mb-6">
        <div class="inline-block p-4 rounded-full bg-red-500/10 border border-red-700/30">
            <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
    </div>

    {{-- Título --}}
    <div class="mb-3 inline-flex rounded-full border border-red-700/40 bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-300">
        {{ $estadoVerificacao['rotulo'] }}
    </div>
    <h1 class="text-3xl font-bold text-red-300 mb-2">{{ $estadoVerificacao['titulo'] }}</h1>

    {{-- Mensagem --}}
    <p class="text-zinc-400 mb-8">
        {{ $estadoVerificacao['mensagem'] ?? $mensagem ?? 'O código QR fornecido não é válido ou o documento não existe mais no sistema.' }}
    </p>

    {{-- Possíveis motivos --}}
    <div class="bg-zinc-900/50 border border-zinc-700/30 rounded-lg p-6 mb-8 text-left">
        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Possíveis motivos:</p>
        <ul class="text-sm text-zinc-400 space-y-1">
            <li>Código QR inválido ou corrompido</li>
            <li>Documento foi removido do sistema</li>
            <li>Erro na leitura do código QR</li>
            <li>Token expirado ou inválido</li>
        </ul>
    </div>

    {{-- Sugestões --}}
    <div class="bg-red-900/20 border border-red-700/30 rounded-lg p-6 mb-8">
        <p class="text-sm text-red-200 mb-3">O que pode fazer:</p>
        <ul class="text-sm text-red-100 space-y-2">
            <li>• Verifique se o código QR foi lido corretamente</li>
            <li>• Entre em contacto com o administrador do sistema</li>
            <li>• Tente novamente com outro código QR</li>
        </ul>
    </div>

    {{-- Botão volta --}}
    <a href="/" class="inline-block px-8 py-3 bg-red-600 hover:bg-red-700 rounded-lg font-medium text-white transition-all">
        ← Voltar ao Início
    </a>

</div>
</body>
</html>
