@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <a href="{{ url()->previous() }}" class="app-header__btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-header__title">
        <span>Parrainage</span>
    </div>
    <div class="app-header__actions"></div>
@endsection

@section('content')
    @auth
        {{-- Hero Section --}}
        <div style="background: var(--cota-accent); color: var(--cota-on-accent); padding: var(--cota-spacing-xl) var(--cota-spacing-md); text-align: center; margin: var(--cota-spacing-md); border-radius: var(--cota-spacing-lg);">
            <div style="width: 64px; height: 64px; background: rgba(11,13,16,0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <i class="bi bi-gift-fill" style="font-size: 2rem; color: var(--cota-on-accent);"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--cota-on-accent); margin-bottom: 8px;">
                Gagnez 7 jours Premium !
            </h2>
            <p style="color: rgba(11,13,16,0.72); font-size: 0.9375rem; margin-bottom: 0;">
                Pour chaque ami qui s'inscrit avec votre code
            </p>
        </div>
        
        {{-- Stats --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 0 var(--cota-spacing-md) var(--cota-spacing-md);">
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px; text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--cota-accent);">{{ $stats['total_referrals'] ?? 0 }}</div>
                <div style="font-size: 0.75rem; color: var(--cota-text-muted);">Parrainages</div>
            </div>
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px; text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--cota-win);">{{ $stats['premium_days_earned'] ?? 0 }}</div>
                <div style="font-size: 0.75rem; color: var(--cota-text-muted);">Jours gagnés</div>
            </div>
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px; text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--cota-favorite);">{{ $stats['pending_rewards'] ?? 0 }}</div>
                <div style="font-size: 0.75rem; color: var(--cota-text-muted);">En attente</div>
            </div>
        </div>
        
        {{-- Referral Code --}}
        <div style="padding: 0 var(--cota-spacing-md) var(--cota-spacing-md);">
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-lg);">
                <p style="font-size: 0.8125rem; color: var(--cota-text-muted); margin-bottom: 8px;">Votre code de parrainage</p>
                
                @php
                    $referralCode = auth()->user()->referral_code ?? strtoupper(substr(md5(auth()->user()->id), 0, 8));
                @endphp
                
                <div style="display: flex; align-items: center; gap: 12px; background: var(--cota-bg-tertiary); border-radius: var(--cota-spacing-sm); padding: 14px 16px; margin-bottom: 16px;">
                    <span style="flex: 1; font-size: 1.25rem; font-weight: 700; letter-spacing: 0.1em; color: var(--cota-text-primary);" id="referralCode">
                        {{ $referralCode }}
                    </span>
                    <button onclick="copyCode()" style="background: var(--cota-accent); border: none; border-radius: 8px; padding: 8px 16px; color: var(--cota-on-accent); font-weight: 800; font-size: 0.8125rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-copy"></i> Copier
                    </button>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                    <button onclick="shareWhatsApp()" class="btn-cota btn-cota--secondary" style="background: #25D366; border-color: #25D366; color: #fff;">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </button>
                    <button onclick="shareGeneric()" class="btn-cota btn-cota--secondary">
                        <i class="bi bi-share"></i> Partager
                    </button>
                </div>
            </div>
        </div>
        
        {{-- How it works --}}
        <div style="padding: 0 var(--cota-spacing-md) var(--cota-spacing-lg);">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--cota-text-primary); margin-bottom: var(--cota-spacing-md);">
                Comment ça marche ?
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @php
                    $steps = [
                        ['num' => '1', 'title' => 'Partagez votre code', 'desc' => 'Envoyez votre code à vos amis'],
                        ['num' => '2', 'title' => 'Inscription', 'desc' => 'Votre ami s\'inscrit avec le code'],
                        ['num' => '3', 'title' => 'Récompense', 'desc' => 'Vous recevez 7 jours Premium gratuits !'],
                    ];
                @endphp
                
                @foreach($steps as $step)
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 32px; height: 32px; background: var(--cota-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--cota-on-accent); flex-shrink: 0;">
                            {{ $step['num'] }}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--cota-text-primary);">{{ $step['title'] }}</div>
                            <div style="font-size: 0.8125rem; color: var(--cota-text-muted);">{{ $step['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- Referrals List --}}
        @if(count($referrals ?? []) > 0)
            <div class="prediction-section" style="margin-bottom: var(--cota-spacing-lg);">
                <div class="prediction-section__header">
                    <i class="bi bi-people"></i>
                    <span>Mes filleuls</span>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($referrals as $referral)
                        <div style="display: flex; align-items: center; padding: 14px var(--cota-spacing-md); border-bottom: 1px solid var(--cota-border);">
                            <div style="width: 40px; height: 40px; background: var(--cota-bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="bi bi-person" style="color: var(--cota-text-muted);"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: var(--cota-text-primary);">{{ $referral['name'] }}</div>
                                <div style="font-size: 0.75rem; color: var(--cota-text-muted);">
                                    {{ \Carbon\Carbon::parse($referral['created_at'])->locale('fr')->diffForHumans() }}
                                </div>
                            </div>
                            <span style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: 500;
                                {{ $referral['status'] === 'completed' ? 'background: rgba(16, 185, 129, 0.15); color: var(--cota-win);' : 'background: var(--cota-bg-tertiary); color: var(--cota-text-muted);' }}">
                                {{ $referral['status'] === 'completed' ? '+7 jours' : 'En attente' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        {{-- Guest View --}}
        <div class="empty-state" style="min-height: 60vh;">
            <div class="empty-state__icon">
                <i class="bi bi-gift"></i>
            </div>
            <h3 class="empty-state__title">Programme de parrainage</h3>
            <p class="empty-state__text">
                Connectez-vous pour accéder à votre code de parrainage et gagner des jours Premium gratuits.
            </p>
            <a href="{{ route('login') }}" class="btn-cota btn-cota--primary">
                <i class="bi bi-box-arrow-in-right"></i> Connexion
            </a>
        </div>
    @endauth
@endsection

@push('scripts')
<script>
    function copyCode() {
        const code = document.getElementById('referralCode').textContent.trim();
        navigator.clipboard.writeText(code).then(() => {
            alert('Code copié !');
        });
    }
    
    function shareWhatsApp() {
        const code = document.getElementById('referralCode').textContent.trim();
        const text = `🎯 Rejoins COTA, la meilleure app de pronostics football !\n\nUtilise mon code : ${code}\n\nTélécharge l'app : https://cota.app`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
    }
    
    function shareGeneric() {
        const code = document.getElementById('referralCode').textContent.trim();
        if (navigator.share) {
            navigator.share({
                title: 'COTA - Pronostics Football',
                text: `Utilise mon code ${code} pour t'inscrire sur COTA !`,
                url: 'https://cota.app'
            });
        } else {
            copyCode();
        }
    }
</script>
@endpush
