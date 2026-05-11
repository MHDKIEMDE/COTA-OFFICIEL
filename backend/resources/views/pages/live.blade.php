@extends('layouts.app')

@php
    $hideDate = true;
    $currentPage = 'live';
@endphp

@section('header')
    <div class="app-header__title">
        <i class="bi bi-broadcast text-danger"></i>
        <span>Matchs en Direct</span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>
@endsection

@section('content')
    {{-- Live Indicator --}}
    <div style="display: flex; align-items: center; justify-content: space-between; padding: var(--cota-spacing-md); background: var(--cota-bg-secondary); margin-bottom: 2px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 8px; height: 8px; background: var(--cota-loss); border-radius: 50%; animation: pulse 2s infinite;"></span>
            <span style="font-weight: 600; color: var(--cota-text-primary);">{{ $liveCount ?? 0 }} matchs en cours</span>
        </div>
        <span style="font-size: 0.75rem; color: var(--cota-text-muted);">
            Mise à jour auto. <i class="bi bi-clock"></i>
        </span>
    </div>
    
    {{-- Live Matches --}}
    @forelse($liveMatches ?? [] as $match)
        <a href="{{ route('predictions.show', $match) }}" class="match-card match-card--live">
            {{-- Live Badge --}}
            <div style="position: absolute; top: 8px; right: 8px;">
                <span style="background: var(--cota-loss); color: #fff; font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                    <i class="bi bi-broadcast"></i> LIVE {{ $match->live_minute ?? '45' }}'
                </span>
            </div>
            
            {{-- Competition --}}
            <div style="font-size: 0.75rem; color: var(--cota-text-muted); margin-bottom: 8px;">
                {{ $match->competition }}
            </div>
            
            {{-- Teams & Score --}}
            <div style="display: flex; align-items: center; gap: 16px;">
                {{-- Home Team --}}
                <div style="flex: 1; text-align: center;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($match->home_team, 0, 3)) }}&size=48&background=21262D&color=fff&bold=true" 
                         alt="" style="width: 48px; height: 48px; border-radius: 8px; margin-bottom: 8px;">
                    <div style="font-weight: 600; color: var(--cota-text-primary); font-size: 0.875rem;">{{ $match->home_team }}</div>
                </div>
                
                {{-- Score --}}
                <div style="text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: var(--cota-text-primary); letter-spacing: 4px;">
                        {{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--cota-loss); font-weight: 600;">
                        {{ $match->live_minute ?? '45' }}'
                    </div>
                </div>
                
                {{-- Away Team --}}
                <div style="flex: 1; text-align: center;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($match->away_team, 0, 3)) }}&size=48&background=21262D&color=fff&bold=true" 
                         alt="" style="width: 48px; height: 48px; border-radius: 8px; margin-bottom: 8px;">
                    <div style="font-weight: 600; color: var(--cota-text-primary); font-size: 0.875rem;">{{ $match->away_team }}</div>
                </div>
            </div>
            
            {{-- Match Events --}}
            @if($match->events ?? false)
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--cota-border);">
                    <div style="display: flex; gap: 12px; justify-content: center; font-size: 0.75rem; color: var(--cota-text-muted);">
                        <span><i class="bi bi-circle-fill" style="font-size: 0.5rem; color: var(--cota-win);"></i> But 23'</span>
                        <span><i class="bi bi-card-text" style="color: #FBBF24;"></i> Carton 41'</span>
                    </div>
                </div>
            @endif
        </a>
    @empty
        {{-- Empty State --}}
        <div class="empty-state" style="min-height: 50vh;">
            <div class="empty-state__icon">
                <i class="bi bi-broadcast"></i>
            </div>
            <h3 class="empty-state__title">Aucun match en direct</h3>
            <p class="empty-state__text">
                Il n'y a pas de matchs en cours pour le moment. Revenez plus tard ou consultez les prochains matchs.
            </p>
            <a href="{{ route('home') }}" class="btn-cota btn-cota--primary">
                <i class="bi bi-calendar-event"></i> Voir les matchs du jour
            </a>
        </div>
    @endforelse
    
    {{-- Upcoming Matches Section --}}
    @if(isset($upcomingMatches) && count($upcomingMatches) > 0)
        <div style="padding: var(--cota-spacing-md) var(--cota-spacing-md) 8px;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--cota-text-primary); display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-clock" style="color: var(--cota-text-muted);"></i>
                À venir aujourd'hui
            </h3>
        </div>
        
        @foreach($upcomingMatches as $match)
            <a href="{{ route('predictions.show', $match) }}" class="match-card">
                <div class="match-card__teams">
                    <div class="match-card__team">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($match->home_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                             alt="" class="match-card__team-logo">
                        <span class="match-card__team-name">{{ $match->home_team }}</span>
                    </div>
                    <div class="match-card__team">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($match->away_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                             alt="" class="match-card__team-logo">
                        <span class="match-card__team-name">{{ $match->away_team }}</span>
                    </div>
                </div>
                <div class="match-card__result">
                    <span class="match-card__time">{{ \Carbon\Carbon::parse($match->match_time)->format('H:i') }}</span>
                    <span class="match-card__status match-card__status--preview">PREVIEW</span>
                </div>
            </a>
        @endforeach
    @endif
    
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
@endsection

