<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0D1117">
    
    <title>{{ config('app.name', 'COTA') }} - @yield('title', 'Authentification')</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Styles --}}
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body style="background: var(--cota-bg-primary); color: var(--cota-text-primary); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0;">
    @yield('content')
    
    @livewireScripts
</body>
</html>
