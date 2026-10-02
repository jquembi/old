<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — {{ config('app.name', 'GTerras') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=merriweather:300,400,700,900" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('imagens/favicon/favicon.ico') }}" type="image/x-icon">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Merriweather', serif;
            background: #18181b;
            color: #fafafa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Fundo com grid subtil */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 50% 50%, rgba(63,63,70,.15) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        /* Glow central */
        .glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .12;
            pointer-events: none;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 480px;
            width: 100%;
        }

        /* Código do erro */
        .error-code {
            font-size: clamp(5rem, 15vw, 9rem);
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(180deg, #fafafa 0%, #71717a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: .25rem;
        }

        .error-title {
            font-size: clamp(1.125rem, 3vw, 1.5rem);
            font-weight: 600;
            color: #e4e4e7;
            margin-bottom: .75rem;
        }

        .error-message {
            font-size: .9375rem;
            color: #a1a1aa;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Ícone animado */
        .icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .icon-wrapper svg {
            width: 32px;
            height: 32px;
            position: relative;
            z-index: 1;
        }

        /* Botões */
        .actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .625rem 1.25rem;
            font-size: .875rem;
            font-weight: 500;
            border-radius: .75rem;
            text-decoration: none;
            transition: all .2s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-primary {
            background: #fafafa;
            color: #18181b;
        }
        .btn-primary:hover {
            background: #e4e4e7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
        }
        .btn-secondary {
            background: rgba(63,63,70,.5);
            color: #d4d4d8;
            border: 1px solid rgba(63,63,70,.8);
        }
        .btn-secondary:hover {
            background: rgba(63,63,70,.8);
            color: #fafafa;
            transform: translateY(-1px);
        }
        .btn svg {
            width: 16px;
            height: 16px;
        }

        /* Separador */
        .divider {
            width: 48px;
            height: 2px;
            border-radius: 1px;
            margin: 0 auto 1.5rem;
            opacity: .4;
        }

        /* Animação */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .icon-wrapper {
            animation: float 4s ease-in-out infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .container {
            animation: fadeIn .6s ease-out;
        }
    </style>
</head>
<body>
    <div class="glow" style="background: @yield('glow-color', '#3b82f6');"></div>

    <div class="container">
        <div class="icon-wrapper" style="background: @yield('icon-bg', 'rgba(59,130,246,.1)');">
            @yield('icon')
        </div>

        <div class="error-code">@yield('code')</div>
        <h1 class="error-title">@yield('title')</h1>
        <div class="divider" style="background: @yield('divider-color', '#3b82f6');"></div>
        <p class="error-message">@yield('message')</p>

        <div class="actions">
            @section('actions')
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    Página Inicial
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                    Voltar
                </a>
            @show
        </div>
    </div>
</body>
</html>
