<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0d10">
    <title>{{ config('app.name', 'COTA') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:   #0b0d10;
            --bg2:  #15181d;
            --bg3:  #1e2028;
            --ink:  #f4efe2;
            --dim:  #b8b4a8;
            --acc:  #e8ff36;
            --win:  #3ddc91;
            --loss: #ff5b3a;
            --line: #2a2e36;
            --line2:#1e2228;
        }
        html, body { height: 100%; background: var(--bg); color: var(--ink); font-family: 'Space Grotesk', sans-serif; }
        .auth-wrap {
            max-width: 430px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: env(safe-area-inset-top) 0 env(safe-area-inset-bottom);
        }
        input, button, textarea, select { font-family: inherit; }
    </style>

    @livewireStyles
</head>
<body>
    <div class="auth-wrap">
        @yield('content')
    </div>
    @livewireScripts
</body>
</html>
