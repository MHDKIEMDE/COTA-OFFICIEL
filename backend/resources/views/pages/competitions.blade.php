@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <div class="app-header__title">
        <i class="bi bi-trophy"></i>
        <span>Championnats</span>
    </div>
    <div class="app-header__actions">
        <a href="#" class="app-header__btn" data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="bi bi-search"></i>
        </a>
    </div>
@endsection

@section('content')
    {{-- Trending Competitions --}}
    <div style="padding: var(--cota-spacing-md);">
        <h3 style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cota-text-muted); margin-bottom: 12px;">
            🔥 Tendances
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
            @php
                $trendingComps = [
                    ['name' => 'CAN 2026', 'icon' => '🏆', 'matches' => 8, 'trending' => true],
                    ['name' => 'Premier League', 'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'matches' => 10],
                    ['name' => 'La Liga', 'icon' => '🇪🇸', 'matches' => 10],
                    ['name' => 'Serie A', 'icon' => '🇮🇹', 'matches' => 10],
                ];
            @endphp
            
            @foreach($trendingComps as $comp)
                <a href="{{ route('predictions.index', ['competition' => $comp['name']]) }}" style="display: flex; align-items: center; gap: 12px; background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 14px; text-decoration: none; {{ isset($comp['trending']) ? 'border: 1px solid var(--cota-accent);' : '' }}">
                    <span style="font-size: 1.5rem;">{{ $comp['icon'] }}</span>
                    <div>
                        <div style="font-weight: 600; color: var(--cota-text-primary); font-size: 0.875rem;">{{ $comp['name'] }}</div>
                        <div style="font-size: 0.75rem; color: var(--cota-text-muted);">{{ $comp['matches'] }} matchs</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    
    {{-- All Competitions by Country --}}
    @forelse($competitions ?? [] as $country => $countryCompetitions)
        <div class="prediction-section" style="margin-top: 0;">
            <div class="prediction-section__header">
                <span>{{ $country }}</span>
            </div>
            <div style="padding: 0;">
                @foreach($countryCompetitions as $competition)
                    <a href="{{ route('predictions.index', ['competition' => $competition->sportradar_id]) }}" 
                       style="display: flex; align-items: center; padding: 14px var(--cota-spacing-md); border-bottom: 1px solid var(--cota-border); text-decoration: none;">
                        <span style="font-size: 1.25rem; margin-right: 14px;">{{ $competition->icon ?? '⚽' }}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 500; color: var(--cota-text-primary);">{{ $competition->name }}</div>
                            @if($competition->full_name && $competition->full_name !== $competition->name)
                                <div style="font-size: 0.75rem; color: var(--cota-text-muted);">{{ $competition->full_name }}</div>
                            @endif
                        </div>
                        @if($competition->is_trending)
                            <span style="background: rgba(239, 68, 68, 0.15); color: var(--cota-loss); font-size: 0.6875rem; padding: 2px 8px; border-radius: 4px; font-weight: 600; margin-right: 8px;">
                                LIVE
                            </span>
                        @endif
                        <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        {{-- Default competitions list --}}
        @php
            $defaultCompetitions = [
                'Europe' => [
                    ['name' => 'Champions League', 'full_name' => 'UEFA Champions League', 'icon' => '⭐'],
                    ['name' => 'Europa League', 'full_name' => 'UEFA Europa League', 'icon' => '🏆'],
                    ['name' => 'Conference League', 'full_name' => 'UEFA Conference League', 'icon' => '🏅'],
                ],
                'Angleterre' => [
                    ['name' => 'Premier League', 'full_name' => 'English Premier League', 'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿'],
                    ['name' => 'FA Cup', 'full_name' => 'The FA Cup', 'icon' => '🏆'],
                    ['name' => 'EFL Cup', 'full_name' => 'League Cup', 'icon' => '🏆'],
                ],
                'Espagne' => [
                    ['name' => 'La Liga', 'full_name' => 'La Liga Santander', 'icon' => '🇪🇸'],
                    ['name' => 'Copa del Rey', 'full_name' => 'Copa del Rey', 'icon' => '🏆'],
                ],
                'Italie' => [
                    ['name' => 'Serie A', 'full_name' => 'Serie A TIM', 'icon' => '🇮🇹'],
                    ['name' => 'Coppa Italia', 'full_name' => 'Coppa Italia', 'icon' => '🏆'],
                ],
                'France' => [
                    ['name' => 'Ligue 1', 'full_name' => 'Ligue 1 Uber Eats', 'icon' => '🇫🇷'],
                    ['name' => 'Coupe de France', 'full_name' => 'Coupe de France', 'icon' => '🏆'],
                ],
                'Allemagne' => [
                    ['name' => 'Bundesliga', 'full_name' => 'Deutsche Bundesliga', 'icon' => '🇩🇪'],
                    ['name' => 'DFB Pokal', 'full_name' => 'DFB Pokal', 'icon' => '🏆'],
                ],
                'Afrique' => [
                    ['name' => 'CAN', 'full_name' => 'Coupe d\'Afrique des Nations', 'icon' => '🌍', 'trending' => true],
                ],
            ];
        @endphp
        
        @foreach($defaultCompetitions as $country => $comps)
            <div class="prediction-section" style="margin-top: 0;">
                <div class="prediction-section__header">
                    <span>{{ $country }}</span>
                </div>
                <div style="padding: 0;">
                    @foreach($comps as $competition)
                        <a href="{{ route('predictions.index', ['search' => $competition['name']]) }}" 
                           style="display: flex; align-items: center; padding: 14px var(--cota-spacing-md); border-bottom: 1px solid var(--cota-border); text-decoration: none;">
                            <span style="font-size: 1.25rem; margin-right: 14px;">{{ $competition['icon'] }}</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: var(--cota-text-primary);">{{ $competition['name'] }}</div>
                                @if(isset($competition['full_name']))
                                    <div style="font-size: 0.75rem; color: var(--cota-text-muted);">{{ $competition['full_name'] }}</div>
                                @endif
                            </div>
                            @if(isset($competition['trending']))
                                <span style="background: rgba(239, 68, 68, 0.15); color: var(--cota-loss); font-size: 0.6875rem; padding: 2px 8px; border-radius: 4px; font-weight: 600; margin-right: 8px;">
                                    🔥 HOT
                                </span>
                            @endif
                            <i class="bi bi-chevron-right" style="color: var(--cota-text-muted);"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endforelse
@endsection

