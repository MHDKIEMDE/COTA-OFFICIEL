@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <a href="{{ url()->previous() }}" class="app-header__btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-header__title">
        <span>Mon Profil</span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn">
            <i class="bi bi-gear"></i>
        </button>
    </div>
@endsection

@section('content')
    @auth
        {{-- Profile Header --}}
        <div style="background: linear-gradient(135deg, var(--cota-bg-tertiary) 0%, var(--cota-bg-secondary) 100%); padding: var(--cota-spacing-xl) var(--cota-spacing-md); text-align: center;">
            <div style="width: 80px; height: 80px; margin: 0 auto 16px; background: var(--cota-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: var(--cota-on-accent); border: 3px solid var(--cota-bg-elevated);">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--cota-text-primary); margin-bottom: 4px;">
                {{ auth()->user()->name }}
            </h2>
            <p style="font-size: 0.875rem; color: var(--cota-text-muted); margin-bottom: 12px;">
                {{ auth()->user()->phone ?? auth()->user()->email }}
            </p>
            
            @if(auth()->user()->is_premium)
                <span style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%); color: #1E293B; padding: 6px 16px; border-radius: 20px; font-size: 0.8125rem; font-weight: 700;">
                    <i class="bi bi-star-fill"></i> PREMIUM
                    @if(auth()->user()->premium_expires_at)
                        · {{ \Carbon\Carbon::parse(auth()->user()->premium_expires_at)->diffForHumans() }}
                    @else
                        · À vie ♾️
                    @endif
                </span>
            @else
                <a href="{{ route('subscription') }}" style="display: inline-flex; align-items: center; gap: 6px; background: var(--cota-bg-tertiary); color: var(--cota-text-primary); padding: 6px 16px; border-radius: 20px; font-size: 0.8125rem; font-weight: 500; text-decoration: none; border: 1px solid var(--cota-border);">
                    <i class="bi bi-arrow-up-circle"></i> Passer Premium
                </a>
            @endif
        </div>
        
        {{-- Menu Items --}}
        <div style="padding: var(--cota-spacing-md);">
            {{-- Account Section --}}
            <p style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cota-text-muted); margin-bottom: 8px; padding-left: 4px;">
                Compte
            </p>
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); overflow: hidden; margin-bottom: var(--cota-spacing-lg);">
                <a href="{{ route('subscription') }}" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-credit-card" style="font-size: 1.25rem; color: var(--cota-accent); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Abonnement</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
                <a href="{{ route('referral') }}" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-gift" style="font-size: 1.25rem; color: var(--cota-favorite); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Parrainage</span>
                    <span style="background: var(--cota-accent); color: var(--cota-on-accent); font-size: 0.6875rem; padding: 2px 8px; border-radius: 10px; font-weight: 800;">+7 jours</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted); margin-left: 8px;"></i>
                </a>
                <a href="#" style="display: flex; align-items: center; padding: 14px 16px; text-decoration: none; color: inherit;">
                    <i class="bi bi-bell" style="font-size: 1.25rem; color: var(--cota-text-muted); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Notifications</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
            </div>
            
            {{-- Stats Section --}}
            <p style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cota-text-muted); margin-bottom: 8px; padding-left: 4px;">
                Activité
            </p>
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); overflow: hidden; margin-bottom: var(--cota-spacing-lg);">
                <a href="{{ route('statistics') }}" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-bar-chart-line" style="font-size: 1.25rem; color: var(--cota-win); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Statistiques</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
                <a href="{{ route('history') }}" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-clock-history" style="font-size: 1.25rem; color: var(--cota-text-muted); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Historique</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
                <a href="#" style="display: flex; align-items: center; padding: 14px 16px; text-decoration: none; color: inherit;">
                    <i class="bi bi-star" style="font-size: 1.25rem; color: var(--cota-favorite); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Favoris</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
            </div>
            
            {{-- Support Section --}}
            <p style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cota-text-muted); margin-bottom: 8px; padding-left: 4px;">
                Aide
            </p>
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); overflow: hidden; margin-bottom: var(--cota-spacing-lg);">
                <a href="#" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-question-circle" style="font-size: 1.25rem; color: var(--cota-text-muted); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">FAQ</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
                <a href="#" style="display: flex; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--cota-border); text-decoration: none; color: inherit;">
                    <i class="bi bi-headset" style="font-size: 1.25rem; color: var(--cota-text-muted); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Support</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
                <a href="#" style="display: flex; align-items: center; padding: 14px 16px; text-decoration: none; color: inherit;">
                    <i class="bi bi-file-text" style="font-size: 1.25rem; color: var(--cota-text-muted); margin-right: 14px;"></i>
                    <span style="flex: 1; color: var(--cota-text-primary);">Conditions d'utilisation</span>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                </a>
            </div>
            
            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--cota-spacing-sm); color: var(--cota-loss); font-weight: 500; cursor: pointer;">
                    <i class="bi bi-box-arrow-right"></i>
                    Déconnexion
                </button>
            </form>
            
            {{-- App Version --}}
            <p style="text-align: center; margin-top: var(--cota-spacing-lg); font-size: 0.75rem; color: var(--cota-text-muted);">
                COTA v2.0 • © 2026
            </p>
        </div>
    @else
        {{-- Guest View --}}
        <div class="empty-state" style="min-height: 60vh;">
            <div class="empty-state__icon">
                <i class="bi bi-person"></i>
            </div>
            <h3 class="empty-state__title">Connectez-vous</h3>
            <p class="empty-state__text">
                Accédez à votre profil, historique et statistiques personnalisées.
            </p>
            <a href="{{ route('login') }}" class="btn-cota btn-cota--primary">
                <i class="bi bi-box-arrow-in-right"></i> Connexion
            </a>
            <a href="{{ route('register') }}" class="btn-cota btn-cota--secondary mt-2">
                Créer un compte
            </a>
        </div>
    @endauth
@endsection
