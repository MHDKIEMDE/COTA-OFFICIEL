@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <div class="app-header__title">
        <i class="bi bi-clock-history"></i>
        <span>Historique</span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn">
            <i class="bi bi-filter"></i>
        </button>
    </div>
@endsection

@section('content')
    {{-- Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; padding: var(--cota-spacing-md);">
        <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 12px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-text-primary);">{{ $stats['total'] ?? 0 }}</div>
            <div style="font-size: 0.6875rem; color: var(--cota-text-muted); text-transform: uppercase;">Total</div>
        </div>
        <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 12px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-win);">{{ $stats['won'] ?? 0 }}</div>
            <div style="font-size: 0.6875rem; color: var(--cota-text-muted); text-transform: uppercase;">Gagnés</div>
        </div>
        <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 12px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-loss);">{{ $stats['lost'] ?? 0 }}</div>
            <div style="font-size: 0.6875rem; color: var(--cota-text-muted); text-transform: uppercase;">Perdus</div>
        </div>
        <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 12px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-accent);">{{ $stats['win_rate'] ?? 0 }}%</div>
            <div style="font-size: 0.6875rem; color: var(--cota-text-muted); text-transform: uppercase;">Taux</div>
        </div>
    </div>
    
    {{-- Tabs --}}
    <div class="cota-tabs">
        <a href="#" class="cota-tabs__item active">Tous</a>
        <a href="#" class="cota-tabs__item">
            <i class="bi bi-check-circle-fill text-win me-1"></i> Gagnés
        </a>
        <a href="#" class="cota-tabs__item">
            <i class="bi bi-x-circle-fill text-loss me-1"></i> Perdus
        </a>
        <a href="#" class="cota-tabs__item">En attente</a>
    </div>
    
    {{-- History List --}}
    @forelse($predictions ?? [] as $prediction)
        <a href="{{ route('predictions.show', $prediction) }}" class="match-card">
            {{-- Status Indicator --}}
            <div style="width: 4px; border-radius: 2px; margin-right: 12px; align-self: stretch; 
                        background: {{ $prediction->status === 'won' ? 'var(--cota-win)' : ($prediction->status === 'lost' ? 'var(--cota-loss)' : 'var(--cota-draw)') }};">
            </div>
            
            {{-- Teams --}}
            <div class="match-card__teams">
                <div class="match-card__team">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->home_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                         alt="" class="match-card__team-logo">
                    <span class="match-card__team-name">{{ $prediction->home_team }}</span>
                </div>
                <div class="match-card__team">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->away_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                         alt="" class="match-card__team-logo">
                    <span class="match-card__team-name">{{ $prediction->away_team }}</span>
                </div>
            </div>
            
            {{-- Result --}}
            <div class="match-card__result">
                <div class="d-flex flex-column align-items-end gap-1">
                    <span style="font-size: 0.75rem; color: var(--cota-text-muted);">
                        {{ \Carbon\Carbon::parse($prediction->match_date)->format('d/m') }}
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; background: var(--cota-bg-tertiary); color: var(--cota-text-secondary);">
                            {{ $prediction->prediction }}
                        </span>
                        @if($prediction->status === 'won')
                            <i class="bi bi-check-circle-fill text-win"></i>
                        @elseif($prediction->status === 'lost')
                            <i class="bi bi-x-circle-fill text-loss"></i>
                        @else
                            <i class="bi bi-clock text-muted-custom"></i>
                        @endif
                    </div>
                    <span style="font-size: 0.875rem; font-weight: 700;">
                        {{ $prediction->home_score ?? '-' }} : {{ $prediction->away_score ?? '-' }}
                    </span>
                </div>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div class="empty-state__icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h3 class="empty-state__title">Aucun historique</h3>
            <p class="empty-state__text">
                @auth
                    Vos pronostics terminés apparaîtront ici.
                @else
                    Connectez-vous pour voir votre historique de pronostics.
                @endauth
            </p>
            @guest
                <a href="{{ route('login') }}" class="btn-cota btn-cota--primary">
                    <i class="bi bi-box-arrow-in-right"></i> Connexion
                </a>
            @endguest
        </div>
    @endforelse
@endsection
