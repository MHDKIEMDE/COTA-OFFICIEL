@extends('layouts.auth')

@section('content')
<style>
    .auth-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 16px;
        color: var(--dim);
        text-decoration: none;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px;
    }
    .auth-logo {
        width: 68px; height: 68px;
        background: var(--acc);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 16px 36px rgba(232,255,54,.16);
    }
    .auth-logo span {
        font-family: 'Archivo', sans-serif;
        font-size: 32px; font-weight: 900;
        color: var(--bg);
    }
    .auth-title {
        font-family: 'Archivo', sans-serif;
        font-size: 26px; font-weight: 900;
        color: var(--ink);
        text-align: center;
        margin-bottom: 6px;
    }
    .auth-sub {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 14px;
        color: var(--dim);
        text-align: center;
        margin-bottom: 32px;
    }
</style>

<a href="{{ route('home') }}" class="auth-back">
    <i class="bi bi-arrow-left"></i> Retour
</a>

<div style="flex:1;padding:0 16px 32px;display:flex;flex-direction:column;">
    <div style="margin-bottom:32px;">
        <div class="auth-logo"><span>C</span></div>
        <div class="auth-title">Connexion</div>
        <div class="auth-sub">Accède à tes pronostics premium</div>
    </div>

    <livewire:auth.login-form />
</div>
@endsection
