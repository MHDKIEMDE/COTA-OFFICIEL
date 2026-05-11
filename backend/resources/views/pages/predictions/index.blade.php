@extends('layouts.app')

@php
    $hideDate = false;
@endphp

@section('content')
    {{-- Favorites Bar --}}
    <div class="favorites-bar">
        <p class="favorites-bar__title">COMPÉTITIONS FAVORITES</p>
        <div class="favorites-bar__scroll">
            @php
                $favoriteIcons = [
                    'CAN' => '🏆',
                    'Premier League' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
                    'La Liga' => '🇪🇸',
                    'Serie A' => '🇮🇹',
                    'Ligue 1' => '🇫🇷',
                    'Bundesliga' => '🇩🇪',
                    'Champions League' => '⭐',
                    'Europa League' => '🌍',
                ];
            @endphp
            
            @foreach($competitions ?? [] as $comp)
                <a href="{{ route('predictions.index', ['competition' => $comp['id'], 'date' => $filters['date'] ?? now()->format('Y-m-d')]) }}" 
                   class="favorites-bar__item {{ ($filters['competition'] ?? '') == $comp['id'] ? 'active' : '' }}">
                    <span class="favorites-bar__flag" style="font-size: 1rem;">{{ $favoriteIcons[$comp['name']] ?? '⚽' }}</span>
                    <span class="favorites-bar__name">{{ $comp['name'] }}</span>
                </a>
            @endforeach
            
            @if(empty($competitions) || count($competitions ?? []) == 0)
                @foreach(['CAN' => '🏆', 'Premier League' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'La Liga' => '🇪🇸', 'Serie A' => '🇮🇹', 'Ligue 1' => '🇫🇷', 'Bundesliga' => '🇩🇪'] as $name => $icon)
                    <a href="{{ route('predictions.index', ['search' => $name]) }}" class="favorites-bar__item">
                        <span class="favorites-bar__flag" style="font-size: 1rem;">{{ $icon }}</span>
                        <span class="favorites-bar__name">{{ $name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
    
    {{-- Filter Pills --}}
    <div class="filter-pills">
        <a href="{{ route('predictions.index', ['date' => $filters['date'] ?? now()->format('Y-m-d')]) }}" 
           class="filter-pills__item {{ empty($filters['confidence']) && empty($filters['competition']) ? 'active' : '' }}">
            Tous les matchs
        </a>
        <a href="{{ route('predictions.index', ['date' => $filters['date'] ?? now()->format('Y-m-d'), 'confidence' => 70]) }}" 
           class="filter-pills__item {{ ($filters['confidence'] ?? 0) >= 70 ? 'active' : '' }}">
            🔥 Tendances
        </a>
        <a href="{{ route('predictions.index', ['date' => $filters['date'] ?? now()->format('Y-m-d'), 'confidence' => 80]) }}" 
           class="filter-pills__item {{ ($filters['confidence'] ?? 0) >= 80 ? 'active' : '' }}">
            ⭐ Premium
        </a>
    </div>
    
    {{-- Predictions grouped by competition --}}
    @php
        $groupedPredictions = $predictions->groupBy('competition');
        $competitionIcons = [
            'CAN' => '🏆',
            'Premier League' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'La Liga' => '🇪🇸',
            'Serie A' => '🇮🇹',
            'Ligue 1' => '🇫🇷',
            'Bundesliga' => '🇩🇪',
            'Champions League' => '⭐',
            'Europa League' => '🌍',
            'Conference League' => '🏅',
        ];
        $trendingCompetitions = ['CAN', 'Premier League', 'La Liga', 'Serie A', 'Ligue 1', 'Bundesliga'];
    @endphp
    
    @forelse($groupedPredictions as $competition => $matches)
        <div class="competition-group">
            <div class="competition-group__header">
                <span class="competition-group__icon" style="font-size: 1.25rem;">
                    {{ $competitionIcons[$competition] ?? '⚽' }}
                </span>
                <div class="competition-group__info">
                    <div class="competition-group__name">{{ $competition }}</div>
                    <div class="competition-group__country">{{ $matches->first()->country ?? 'Football' }}</div>
                </div>
                @if(in_array($competition, $trendingCompetitions))
                    <span class="competition-group__badge competition-group__trending">
                        🔥 TENDANCE
                    </span>
                @endif
                <span class="competition-group__badge">{{ $matches->count() }}</span>
            </div>
            
            @foreach($matches as $prediction)
                <a href="{{ route('predictions.show', $prediction) }}" class="match-card {{ $prediction->status === 'live' ? 'match-card--live' : '' }}">
                    {{-- Favorite Star --}}
                    <div class="match-card__favorite" onclick="event.preventDefault(); toggleFavorite({{ $prediction->id }})">
                        <i class="bi bi-star"></i>
                    </div>
                    
                    {{-- Teams --}}
                    <div class="match-card__teams">
                        <div class="match-card__team {{ $prediction->status === 'finished' && ($prediction->home_score ?? 0) > ($prediction->away_score ?? 0) ? 'match-card__team--win' : '' }}">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->home_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                                 alt="" class="match-card__team-logo">
                            <span class="match-card__team-name">{{ $prediction->home_team }}</span>
                        </div>
                        <div class="match-card__team {{ $prediction->status === 'finished' && ($prediction->away_score ?? 0) > ($prediction->home_score ?? 0) ? 'match-card__team--win' : '' }}">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->away_team, 0, 3)) }}&size=40&background=21262D&color=fff&bold=true" 
                                 alt="" class="match-card__team-logo">
                            <span class="match-card__team-name">{{ $prediction->away_team }}</span>
                        </div>
                    </div>
                    
                    {{-- Result/Time --}}
                    <div class="match-card__result">
                        @if($prediction->status === 'live')
                            <span class="match-card__live-time">{{ $prediction->live_minute ?? '45' }}'</span>
                            <div class="match-card__score">
                                <span class="match-card__score-value">{{ $prediction->home_score ?? 0 }}</span>
                                <span class="match-card__score-value">{{ $prediction->away_score ?? 0 }}</span>
                            </div>
                        @elseif($prediction->status === 'finished' || $prediction->status === 'won' || $prediction->status === 'lost')
                            <span class="match-card__status match-card__status--finished">
                                @if($prediction->status === 'won')
                                    <i class="bi bi-check-circle-fill text-win"></i>
                                @elseif($prediction->status === 'lost')
                                    <i class="bi bi-x-circle-fill text-loss"></i>
                                @endif
                            </span>
                            <div class="match-card__score">
                                <span class="match-card__score-value">{{ $prediction->home_score ?? '-' }}</span>
                                <span class="match-card__score-value">{{ $prediction->away_score ?? '-' }}</span>
                            </div>
                        @else
                            <span class="match-card__time">{{ \Carbon\Carbon::parse($prediction->match_time)->format('H:i') }}</span>
                            <span class="match-card__status match-card__status--preview">PREVIEW</span>
                        @endif
                    </div>
                    
                    {{-- Prediction Indicator --}}
                    @if($prediction->confidence_stars >= 2)
                        <div class="match-card__prediction">
                            <span class="match-card__prediction-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                            <span class="match-card__prediction-stars">
                                @for($i = 0; $i < min($prediction->confidence_stars, 4); $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @empty
        {{-- Empty State --}}
        <div class="empty-state">
            <div class="empty-state__icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="empty-state__title">Aucun pronostic disponible</h3>
            <p class="empty-state__text">
                Il n'y a pas de pronostics pour cette date. Sélectionnez une autre date ou revenez plus tard.
            </p>
            <a href="{{ route('predictions.index') }}" class="btn-cota btn-cota--primary">
                <i class="bi bi-arrow-left"></i> Voir aujourd'hui
            </a>
        </div>
    @endforelse
@endsection

@push('scripts')
<script>
    function toggleFavorite(id) {
        const star = event.target.closest('.match-card__favorite');
        star.classList.toggle('active');
        const icon = star.querySelector('i');
        icon.classList.toggle('bi-star');
        icon.classList.toggle('bi-star-fill');
    }
</script>
@endpush
