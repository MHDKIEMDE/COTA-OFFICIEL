@extends('layouts.app')

@php
    $hideDate = false;
@endphp

@section('content')
    {{-- Competitions du jour (dynamique) --}}
    @php
        $selectedDate = request('date', now()->format('Y-m-d'));
        $isToday = $selectedDate === now()->format('Y-m-d');

        // Mapping drapeaux/icônes par mots-clés dans le nom de la compétition
        $flagMap = [
            'Premier League'         => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'La Liga'                => '🇪🇸',
            'Serie A'                => '🇮🇹',
            'Ligue 1'                => '🇫🇷',
            'Bundesliga'             => '🇩🇪',
            'Champions League'       => '🏆',
            'Europa League'          => '🟠',
            'Conference League'      => '🟢',
            'Liga Portugal'          => '🇵🇹',
            'Eredivisie'             => '🇳🇱',
            'Saudi'                  => '🇸🇦',
            'CAN'                    => '🌍',
            'Africa'                 => '🌍',
            'Copa America'           => '🌎',
            'World Cup'              => '🌐',
            'Championship'           => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'Ligue 2'                => '🇫🇷',
            'Serie B'                => '🇮🇹',
            'Segunda'                => '🇪🇸',
            'MLS'                    => '🇺🇸',
            'Brasileirao'            => '🇧🇷',
            'Argentina'              => '🇦🇷',
        ];

        $getCompetitionFlag = function(string $name, array $map): string {
            foreach ($map as $keyword => $flag) {
                if (str_contains($name, $keyword)) return $flag;
            }
            return '⚽';
        };
    @endphp

    @if(($favoriteCompetitions ?? collect())->isNotEmpty())
    <div class="comp-carousel" id="compCarousel">

        {{-- Header avec titre + flèches de navigation --}}
        <div class="comp-carousel__header">
            <p class="comp-carousel__title">
                @if($isToday)
                    <span class="comp-carousel__live-pulse"></span>
                    COMPÉTITIONS AUJOURD'HUI
                @else
                    <i class="bi bi-calendar3" style="font-size:0.7rem;"></i>
                    COMPÉTITIONS DU {{ \Carbon\Carbon::parse($selectedDate)->locale('fr')->isoFormat('D MMM') }}
                @endif
            </p>
            <div class="comp-carousel__controls">
                <button class="comp-carousel__btn" id="compPrev" aria-label="Précédent">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="comp-carousel__btn" id="compNext" aria-label="Suivant">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        {{-- Piste de défilement --}}
        <div class="comp-carousel__track" id="compTrack">
            @foreach($favoriteCompetitions as $fav)
                <a href="?date={{ $selectedDate }}&competition={{ urlencode($fav['name']) }}"
                   class="comp-carousel__item {{ $fav['live'] ? 'comp-carousel__item--live' : '' }}">

                    <span class="comp-carousel__flag">
                        {{ $getCompetitionFlag($fav['name'], $flagMap) }}
                    </span>

                    <div class="comp-carousel__info">
                        <span class="comp-carousel__name">{{ $fav['name'] }}</span>
                        <span class="comp-carousel__meta">
                            {{ $fav['count'] }} match{{ $fav['count'] > 1 ? 's' : '' }}
                            @if($fav['live'])
                                &nbsp;<span class="comp-carousel__live-tag">LIVE</span>
                            @endif
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
    @endif
    
    {{-- Filter Pills --}}
    <div class="filter-pills">
        <span class="filter-pills__item active">Tous les matchs</span>
        <span class="filter-pills__item">🔥 Tendances</span>
        <span class="filter-pills__item">⭐ Premium</span>
        <span class="filter-pills__item">Confiance élevée</span>
    </div>
    
    {{-- Predictions by Competition --}}
    @forelse($predictions ?? [] as $competition => $matches)
        <div class="competition-group">
            <div class="competition-group__header">
                <span class="competition-group__icon" style="font-size: 1.25rem;">
                    {{ $competitionFlags[$competition] ?? '⚽' }}
                </span>
                <div class="competition-group__info">
                    <div class="competition-group__name">{{ $competition }}</div>
                    <div class="competition-group__country">{{ $matches->first()->country ?? 'FOOTBALL' }}</div>
                </div>
                @if(in_array($competition, ['CAN', 'Premier League', 'La Liga', 'Serie A', 'Ligue 1', 'Bundesliga', 'UEFA Champions League']))
                    <span class="competition-group__badge competition-group__trending">
                        🔥 TENDANCE
                    </span>
                @endif
            </div>

            @foreach($matches as $prediction)
                <a href="{{ route('predictions.show', $prediction) }}" class="match-card {{ $prediction->status === 'live' ? 'match-card--live' : '' }}">
                    {{-- Favorite Star --}}
                    <div class="match-card__favorite" onclick="event.preventDefault(); toggleFavorite({{ $prediction->id }})">
                        <i class="bi bi-star"></i>
                    </div>

                    {{-- Teams --}}
                    <div class="match-card__teams">
                        <div class="match-card__team">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($prediction->home_team) }}&size=40&background=21262D&color=fff"
                                 alt="{{ $prediction->home_team }}" class="match-card__team-logo">
                            <span class="match-card__team-name">{{ $prediction->home_team }}</span>
                        </div>
                        <div class="match-card__team">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($prediction->away_team) }}&size=40&background=21262D&color=fff"
                                 alt="{{ $prediction->away_team }}" class="match-card__team-logo">
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
                        @elseif(in_array($prediction->status, ['won', 'lost', 'cancelled']))
                            <span class="match-card__status match-card__status--finished">TERMINÉ</span>
                            <div class="match-card__score">
                                <span class="match-card__score-value">{{ $prediction->home_score ?? 0 }}</span>
                                <span class="match-card__score-value">{{ $prediction->away_score ?? 0 }}</span>
                            </div>
                        @else
                            <span class="match-card__time">{{ \Carbon\Carbon::parse($prediction->match_date)->format('H:i') }}</span>
                            <span class="match-card__status match-card__status--preview">PREVIEW</span>
                        @endif
                    </div>

                    {{-- Prediction Indicator --}}
                    @if($prediction->confidence_stars >= 3)
                        <div class="match-card__prediction">
                            <span class="match-card__prediction-icon"><i class="bi bi-check-circle-fill"></i></span>
                            <span class="match-card__prediction-stars">
                                @for($i = 0; $i < $prediction->confidence_stars; $i++)
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

    // ---- Carousel Compétitions ----
    (function () {
        const track = document.getElementById('compTrack');
        const btnPrev = document.getElementById('compPrev');
        const btnNext = document.getElementById('compNext');
        if (!track || !btnPrev || !btnNext) return;

        const STEP = 200; // pixels par clic

        function updateButtons() {
            btnPrev.disabled = track.scrollLeft <= 0;
            btnNext.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
        }

        btnPrev.addEventListener('click', () => {
            track.scrollBy({ left: -STEP, behavior: 'smooth' });
        });

        btnNext.addEventListener('click', () => {
            track.scrollBy({ left: STEP, behavior: 'smooth' });
        });

        track.addEventListener('scroll', updateButtons, { passive: true });

        // Auto-scroll : avance toutes les 4 secondes si suffisamment de compétitions
        let autoScroll;
        function startAutoScroll() {
            autoScroll = setInterval(() => {
                const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
                if (atEnd) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: STEP, behavior: 'smooth' });
                }
            }, 4000);
        }

        // Pause sur hover/touch
        track.addEventListener('mouseenter', () => clearInterval(autoScroll));
        track.addEventListener('mouseleave', startAutoScroll);
        track.addEventListener('touchstart', () => clearInterval(autoScroll), { passive: true });
        track.addEventListener('touchend', () => { setTimeout(startAutoScroll, 2000); }, { passive: true });

        // Démarrer si plus de 4 items
        if (track.children.length > 4) startAutoScroll();

        updateButtons();
    })();
</script>
@endpush
