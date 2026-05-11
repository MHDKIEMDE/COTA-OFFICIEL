@extends('layouts.auth')

@section('content')
    <div style="min-height: 100vh; display: flex; flex-direction: column; background: var(--cota-bg-primary);">
        {{-- Header --}}
        <div style="padding: var(--cota-spacing-lg) var(--cota-spacing-md);">
            <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--cota-text-muted); text-decoration: none; font-size: 0.875rem;">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
        
        {{-- Content --}}
        <div style="flex: 1; padding: var(--cota-spacing-md); display: flex; flex-direction: column;">
            {{-- Logo & Title --}}
            <div style="text-align: center; margin-bottom: var(--cota-spacing-xl);">
                <div style="width: 72px; height: 72px; background: var(--cota-accent); border: 1px solid rgba(232, 255, 54, 0.45); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 16px 36px rgba(232, 255, 54, 0.16);">
                    <span style="font-size: 2rem; font-weight: 800; color: var(--cota-on-accent);">C</span>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--cota-text-primary); margin-bottom: 8px;">
                    Connexion
                </h1>
                <p style="color: var(--cota-text-muted); font-size: 0.9375rem;">
                    Accédez à vos pronostics premium
                </p>
            </div>
            
            {{-- Login Form (contient tout: formulaire, séparateur, boutons sociaux, liens) --}}
            <livewire:auth.login-form />
        </div>
    </div>
@endsection
