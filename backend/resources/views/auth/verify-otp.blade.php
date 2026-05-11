@extends('layouts.auth')

@section('content')
    <div style="min-height: 100vh; display: flex; flex-direction: column; background: var(--cota-bg-primary);">
        {{-- Header --}}
        <div style="padding: var(--cota-spacing-lg) var(--cota-spacing-md);">
            <a href="{{ route('login') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--cota-text-muted); text-decoration: none; font-size: 0.875rem;">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
        
        {{-- Content --}}
        <div style="flex: 1; padding: var(--cota-spacing-md); display: flex; flex-direction: column;">
            {{-- Icon & Title --}}
            <div style="text-align: center; margin-bottom: var(--cota-spacing-xl);">
                <div style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <i class="bi bi-shield-lock-fill" style="font-size: 2rem; color: var(--cota-win);"></i>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--cota-text-primary); margin-bottom: 8px;">
                    Vérification OTP
                </h1>
                <p style="color: var(--cota-text-muted); font-size: 0.9375rem;">
                    Un code a été envoyé au<br>
                    <strong style="color: var(--cota-text-primary);">{{ session('otp_identifier', 'votre numéro') }}</strong>
                </p>
            </div>
            
            {{-- OTP Form --}}
            <livewire:auth.verify-otp-form :identifier="session('otp_identifier')" />
            
            {{-- Info --}}
            <div style="margin-top: auto; padding-top: var(--cota-spacing-lg); text-align: center;">
                <p style="font-size: 0.75rem; color: var(--cota-text-muted);">
                    Le code expire dans 5 minutes.
                </p>
            </div>
        </div>
    </div>
@endsection
