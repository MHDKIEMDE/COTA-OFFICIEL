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
                    Créer un compte
                </h1>
                <p style="color: var(--cota-text-muted); font-size: 0.9375rem;">
                    Rejoignez la communauté COTA
                </p>
            </div>
            
            {{-- Register Form --}}
            <livewire:auth.register-form />
            
            {{-- Divider --}}
            <div style="display: flex; align-items: center; gap: 16px; margin: var(--cota-spacing-lg) 0;">
                <div style="flex: 1; height: 1px; background: var(--cota-border);"></div>
                <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">ou</span>
                <div style="flex: 1; height: 1px; background: var(--cota-border);"></div>
            </div>
            
            {{-- Social Login --}}
            <button style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 12px; padding: 14px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-weight: 500; cursor: pointer; margin-bottom: var(--cota-spacing-md);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continuer avec Google
            </button>
            
            <button style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 12px; padding: 14px; background: #1877F2; border: none; border-radius: var(--cota-spacing-sm); color: #fff; font-weight: 500; cursor: pointer;">
                <i class="bi bi-facebook" style="font-size: 1.25rem;"></i>
                Continuer avec Facebook
            </button>
            
            {{-- Terms --}}
            <p style="text-align: center; margin-top: var(--cota-spacing-md); font-size: 0.75rem; color: var(--cota-text-muted);">
                En créant un compte, vous acceptez nos 
                <a href="#" style="color: var(--cota-accent);">Conditions d'utilisation</a> et 
                <a href="#" style="color: var(--cota-accent);">Politique de confidentialité</a>
            </p>
            
            {{-- Login Link --}}
            <p style="text-align: center; margin-top: auto; padding-top: var(--cota-spacing-lg); color: var(--cota-text-muted); font-size: 0.9375rem;">
                Déjà un compte ?
                <a href="{{ route('login') }}" style="color: var(--cota-accent); font-weight: 600; text-decoration: none;">
                    Se connecter
                </a>
            </p>
        </div>
    </div>
@endsection
