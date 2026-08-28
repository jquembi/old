<!DOCTYPE html>
<html lang="pt" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Painel do Sistema' }} — INOTU</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
    @fluxAppearance
    <style>
        .painel-scrollbar::-webkit-scrollbar { width: 5px; }
        .painel-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .painel-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 999px; }
        .painel-scrollbar::-webkit-scrollbar-thumb:hover { background: #52525b; }
    </style>
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    {{ $slot }}
    <x-toast-container />
    @fluxScripts
</body>
</html>
