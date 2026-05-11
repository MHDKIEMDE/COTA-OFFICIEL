@extends('layouts.app')

@php
    $hideDate = true;
    $currentPage = 'favorites';
@endphp

@section('header')
    <div class="app-header__title">
        <i class="bi bi-star-fill" style="color: var(--cota-favorite);"></i>
        <span>Favoris</span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn">
            <i class="bi bi-sliders"></i>
        </button>
    </div>
@endsection

@section('content')
    {{-- Tabs --}}
    <div class="cota-tabs">
        <a href="#" class="cota-tabs__item active" data-tab="matches">Matchs</a>
        <a href="#" class="cota-tabs__item" data-tab="teams">Équipes</a>
        <a href="#" class="cota-tabs__item" data-tab="competitions">Compétitions</a>
    </div>
    
    {{-- Matches Tab --}}
    <div class="tab-pane active" id="matches">
        @auth
            @php
                // Simulated favorite matches (in real app, fetch from user favorites)
                $favoriteMatches = [];
            @endphp
            
            @forelse($favoriteMatches as $match)
                <a href="{{ route('predictions.show', $match) }}" class="match-card">
                    {{-- Match content --}}
                </a>
            @empty
                <div class="empty-state" style="min-height: 40vh;">
                    <div class="empty-state__icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <h3 class="empty-state__title">Aucun match favori</h3>
                    <p class="empty-state__text">
                        Ajoutez des matchs en favoris en appuyant sur l'étoile ⭐ pour les retrouver ici.
                    </p>
                    <a href="{{ route('home') }}" class="btn-cota btn-cota--primary">
                        <i class="bi bi-search"></i> Explorer les matchs
                    </a>
                </div>
            @endforelse
        @else
            <div class="empty-state" style="min-height: 40vh;">
                <div class="empty-state__icon">
                    <i class="bi bi-person"></i>
                </div>
                <h3 class="empty-state__title">Connectez-vous</h3>
                <p class="empty-state__text">
                    Connectez-vous pour sauvegarder vos matchs, équipes et compétitions favoris.
                </p>
                <a href="{{ route('login') }}" class="btn-cota btn-cota--primary">
                    <i class="bi bi-box-arrow-in-right"></i> Connexion
                </a>
            </div>
        @endauth
    </div>
    
    {{-- Teams Tab --}}
    <div class="tab-pane" id="teams" style="display: none;">
        <div class="empty-state" style="min-height: 40vh;">
            <div class="empty-state__icon">
                <i class="bi bi-people"></i>
            </div>
            <h3 class="empty-state__title">Aucune équipe favorite</h3>
            <p class="empty-state__text">
                Suivez vos équipes préférées pour ne manquer aucun de leurs matchs.
            </p>
        </div>
    </div>
    
    {{-- Competitions Tab --}}
    <div class="tab-pane" id="competitions" style="display: none;">
        @php
            $popularCompetitions = [
                ['name' => 'CAN 2026', 'icon' => '🏆', 'country' => 'Afrique'],
                ['name' => 'Premier League', 'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'country' => 'Angleterre'],
                ['name' => 'La Liga', 'icon' => '🇪🇸', 'country' => 'Espagne'],
                ['name' => 'Serie A', 'icon' => '🇮🇹', 'country' => 'Italie'],
                ['name' => 'Ligue 1', 'icon' => '🇫🇷', 'country' => 'France'],
                ['name' => 'Bundesliga', 'icon' => '🇩🇪', 'country' => 'Allemagne'],
            ];
        @endphp
        
        <div style="padding: var(--cota-spacing-md);">
            <p style="font-size: 0.75rem; color: var(--cota-text-muted); margin-bottom: 12px;">SUGGESTIONS</p>
            
            @foreach($popularCompetitions as $comp)
                <div style="display: flex; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--cota-border);">
                    <span style="font-size: 1.5rem; margin-right: 14px;">{{ $comp['icon'] }}</span>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--cota-text-primary);">{{ $comp['name'] }}</div>
                        <div style="font-size: 0.75rem; color: var(--cota-text-muted);">{{ $comp['country'] }}</div>
                    </div>
                    <button style="background: var(--cota-bg-tertiary); border: none; padding: 8px 16px; border-radius: 20px; color: var(--cota-text-primary); font-weight: 500; font-size: 0.8125rem; cursor: pointer;">
                        <i class="bi bi-plus"></i> Suivre
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.cota-tabs__item').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.cota-tabs__item').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
            
            this.classList.add('active');
            const targetId = this.getAttribute('data-tab');
            document.getElementById(targetId).style.display = 'block';
        });
    });
</script>
@endpush

