@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <a href="{{ url()->previous() }}" class="app-header__btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-header__title">
        <span>Abonnement</span>
    </div>
    <div class="app-header__actions"></div>
@endsection

@section('content')
    {{-- Current Status --}}
    @auth
        @if(auth()->user()->is_premium)
            <div style="margin: var(--cota-spacing-md); background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(251, 191, 36, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-lg);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-star-fill" style="color: #1E293B; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--cota-favorite); font-size: 1.125rem;">Membre Premium</div>
                        <div style="font-size: 0.8125rem; color: var(--cota-text-secondary);">
                            @if(auth()->user()->premium_expires_at)
                                Expire {{ \Carbon\Carbon::parse(auth()->user()->premium_expires_at)->locale('fr')->diffForHumans() }}
                            @else
                                Abonnement à vie ♾️
                            @endif
                        </div>
                    </div>
                </div>
                <div style="font-size: 0.8125rem; color: var(--cota-text-secondary);">
                    Profitez de tous les pronostics premium et des analyses détaillées !
                </div>
            </div>
        @endif
    @endauth
    
    {{-- Benefits --}}
    <div style="padding: var(--cota-spacing-md);">
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--cota-text-primary); margin-bottom: var(--cota-spacing-md);">
            Avantages Premium
        </h3>
        
        <div style="display: grid; gap: 12px;">
            @php
                $benefits = [
                    ['icon' => 'bi-lightning-charge-fill', 'color' => '#3B82F6', 'title' => 'Pronostics 3-4 étoiles', 'desc' => 'Accès à tous les pronostics premium'],
                    ['icon' => 'bi-bell-fill', 'color' => '#F59E0B', 'title' => 'Alertes en temps réel', 'desc' => 'Notifications push instantanées'],
                    ['icon' => 'bi-bar-chart-line-fill', 'color' => '#10B981', 'title' => 'Analyses détaillées', 'desc' => 'Statistiques et tendances avancées'],
                    ['icon' => 'bi-headset', 'color' => '#6366F1', 'title' => 'Support prioritaire', 'desc' => 'Assistance rapide via WhatsApp'],
                ];
            @endphp
            
            @foreach($benefits as $benefit)
                <div style="display: flex; align-items: center; gap: 14px; background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 14px;">
                    <div style="width: 44px; height: 44px; background: {{ $benefit['color'] }}20; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi {{ $benefit['icon'] }}" style="color: {{ $benefit['color'] }}; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--cota-text-primary); font-size: 0.9375rem;">{{ $benefit['title'] }}</div>
                        <div style="font-size: 0.8125rem; color: var(--cota-text-muted);">{{ $benefit['desc'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Plans --}}
    <div style="padding: 0 var(--cota-spacing-md) var(--cota-spacing-xl);">
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--cota-text-primary); margin-bottom: var(--cota-spacing-md);">
            Choisir un plan
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($plans ?? [] as $plan)
                <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-lg); border: 2px solid {{ isset($plan['popular']) ? 'var(--cota-accent)' : 'var(--cota-border)' }}; position: relative;">
                    @if(isset($plan['popular']))
                        <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--cota-accent); color: var(--cota-on-accent); font-size: 0.6875rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase;">
                            Populaire
                        </span>
                    @endif
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <div style="font-weight: 600; color: var(--cota-text-primary);">{{ $plan['name'] }}</div>
                            <div style="font-size: 0.8125rem; color: var(--cota-text-muted);">{{ $plan['duration'] }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.75rem; font-weight: 800; color: var(--cota-text-primary);">
                                {{ number_format($plan['price'], 0, ',', ' ') }}
                                <span style="font-size: 0.875rem; font-weight: 400; color: var(--cota-text-muted);">FCFA</span>
                            </div>
                            @if(isset($plan['savings']))
                                <span style="font-size: 0.75rem; color: var(--cota-win); font-weight: 600;">
                                    -{{ $plan['savings'] }} d'économie
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <ul style="margin: 12px 0; padding-left: 0; list-style: none;">
                        @foreach($plan['features'] as $feature)
                            <li style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--cota-text-secondary); margin-bottom: 6px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--cota-win);"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                    
                    <button class="btn-cota {{ isset($plan['popular']) ? 'btn-cota--primary' : 'btn-cota--secondary' }} btn-cota--block">
                        Choisir ce plan
                    </button>
                </div>
            @endforeach
            
            @if(empty($plans))
                {{-- Default plans --}}
                @php
                    $defaultPlans = [
                        ['name' => 'Hebdomadaire', 'price' => 2000, 'duration' => '7 jours', 'features' => ['Pronostics 3-4 étoiles', 'Alertes temps réel']],
                        ['name' => 'Mensuel', 'price' => 5000, 'duration' => '30 jours', 'popular' => true, 'savings' => '30%', 'features' => ['Tous les pronostics', 'Alertes temps réel', 'Analyses détaillées', 'Support prioritaire']],
                        ['name' => 'Annuel', 'price' => 40000, 'duration' => '365 jours', 'savings' => '50%', 'features' => ['Tous les avantages', 'Badge VIP', 'Support WhatsApp dédié']],
                    ];
                @endphp
                
                @foreach($defaultPlans as $plan)
                    <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-lg); border: 2px solid {{ isset($plan['popular']) ? 'var(--cota-accent)' : 'var(--cota-border)' }}; position: relative;">
                        @if(isset($plan['popular']))
                            <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--cota-accent); color: var(--cota-on-accent); font-size: 0.6875rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase;">
                                Populaire
                            </span>
                        @endif
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div>
                                <div style="font-weight: 600; color: var(--cota-text-primary);">{{ $plan['name'] }}</div>
                                <div style="font-size: 0.8125rem; color: var(--cota-text-muted);">{{ $plan['duration'] }}</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.75rem; font-weight: 800; color: var(--cota-text-primary);">
                                    {{ number_format($plan['price'], 0, ',', ' ') }}
                                    <span style="font-size: 0.875rem; font-weight: 400; color: var(--cota-text-muted);">FCFA</span>
                                </div>
                                @if(isset($plan['savings']))
                                    <span style="font-size: 0.75rem; color: var(--cota-win); font-weight: 600;">
                                        -{{ $plan['savings'] }} d'économie
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <ul style="margin: 12px 0; padding-left: 0; list-style: none;">
                            @foreach($plan['features'] as $feature)
                                <li style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--cota-text-secondary); margin-bottom: 6px;">
                                    <i class="bi bi-check-circle-fill" style="color: var(--cota-win);"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        
                        <button class="btn-cota {{ isset($plan['popular']) ? 'btn-cota--primary' : 'btn-cota--secondary' }} btn-cota--block">
                            Choisir ce plan
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    
    {{-- Payment Methods --}}
    <div style="padding: 0 var(--cota-spacing-md) var(--cota-spacing-xl);">
        <p style="font-size: 0.75rem; color: var(--cota-text-muted); text-align: center;">
            Paiement sécurisé via Mobile Money
        </p>
        <div style="display: flex; justify-content: center; gap: 16px; margin-top: 8px;">
            <span style="font-size: 0.8125rem; color: var(--cota-text-secondary);">🟠 Orange Money</span>
            <span style="font-size: 0.8125rem; color: var(--cota-text-secondary);">🟡 MTN MoMo</span>
            <span style="font-size: 0.8125rem; color: var(--cota-text-secondary);">🔵 Wave</span>
        </div>
    </div>
@endsection
