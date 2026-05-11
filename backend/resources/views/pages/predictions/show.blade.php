@extends('layouts.app')

@php $hideDate = true; @endphp

@section('header')
    <a href="{{ url()->previous() }}" class="app-header__btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-header__title">
        <span style="font-size:0.8125rem; color:var(--cota-text-muted);">
            {{ $prediction->competition ?? 'Détail du match' }}
        </span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn" id="favBtn" onclick="toggleFavorite({{ $prediction->id }})">
            <i class="bi bi-star"></i>
        </button>
        <button class="app-header__btn">
            <i class="bi bi-share"></i>
        </button>
    </div>
@endsection

@section('content')

{{-- ===== WRAPPER DESKTOP 2 COLONNES ===== --}}
<div class="match-detail-layout">

{{-- ===== COLONNE PRINCIPALE ===== --}}
<div class="match-detail-main">

{{-- ===== HEADER MATCH ===== --}}
<div class="mh {{ $prediction->status === 'live' ? 'mh--live' : '' }}">

    {{-- Fond atmosphérique --}}
    <div class="mh__bg">
        <div class="mh__bg-glow mh__bg-glow--left"></div>
        <div class="mh__bg-glow mh__bg-glow--right"></div>
    </div>

    {{-- Competition badge --}}
    <div class="mh__competition">
        <span class="mh__competition-badge">
            ⚽ {{ $prediction->competition ?? 'Football' }}
        </span>
        @if($prediction->status === 'live')
            <span class="mh__live-badge">
                <span class="mh__live-dot"></span>
                LIVE
            </span>
        @elseif(in_array($prediction->status, ['won', 'lost', 'finished']))
            <span class="mh__ended-badge">TERMINÉ</span>
        @endif
    </div>

    {{-- VS Section --}}
    <div class="mh__vs">

        {{-- Équipe domicile --}}
        <div class="mh__team mh__team--home">
            <div class="mh__logo-wrap">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->home_team, 0, 3)) }}&size=128&background=21262D&color=fff&bold=true"
                     alt="{{ $prediction->home_team }}" class="mh__logo">
            </div>
            <span class="mh__team-name">{{ $prediction->home_team }}</span>
            <span class="mh__team-role">Domicile</span>
        </div>

        {{-- Centre : score ou heure --}}
        <div class="mh__center">
            @if($prediction->status === 'live')
                <div class="mh__score mh__score--live">
                    <span>{{ $prediction->home_score ?? 0 }}</span>
                    <span class="mh__score-sep">–</span>
                    <span>{{ $prediction->away_score ?? 0 }}</span>
                </div>
                <span class="mh__minute">{{ $prediction->live_minute ?? '?' }}'</span>
            @elseif(in_array($prediction->status, ['won', 'lost', 'finished', 'cancelled']))
                <div class="mh__score">
                    <span>{{ $prediction->home_score ?? '-' }}</span>
                    <span class="mh__score-sep">–</span>
                    <span>{{ $prediction->away_score ?? '-' }}</span>
                </div>
                <span class="mh__time-label">Score final</span>
            @else
                <div class="mh__time-block">
                    <span class="mh__time-value">
                        {{ \Carbon\Carbon::parse($prediction->match_time ?? $prediction->match_date)->format('H:i') }}
                    </span>
                    <span class="mh__time-label">
                        {{ \Carbon\Carbon::parse($prediction->match_date)->locale('fr')->isoFormat('ddd D MMM') }}
                    </span>
                </div>
                <span class="mh__vs-label">VS</span>
            @endif
        </div>

        {{-- Équipe extérieure --}}
        <div class="mh__team mh__team--away">
            <div class="mh__logo-wrap">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(substr($prediction->away_team, 0, 3)) }}&size=128&background=21262D&color=fff&bold=true"
                     alt="{{ $prediction->away_team }}" class="mh__logo">
            </div>
            <span class="mh__team-name">{{ $prediction->away_team }}</span>
            <span class="mh__team-role">Extérieur</span>
        </div>
    </div>

    {{-- Date complète --}}
    <div class="mh__date">
        <i class="bi bi-calendar3"></i>
        {{ \Carbon\Carbon::parse($prediction->match_date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
    </div>
</div>

{{-- ===== TABS ===== --}}
<div class="cota-tabs" id="matchTabs">
    <a href="#resume" class="cota-tabs__item active" data-tab="resume">
        <i class="bi bi-lightning-charge"></i> Résumé
    </a>
    <a href="#h2h" class="cota-tabs__item" data-tab="h2h">
        <i class="bi bi-arrow-left-right"></i> H2H
    </a>
    <a href="#classements" class="cota-tabs__item" data-tab="classements">
        <i class="bi bi-list-ol"></i> Classements
    </a>
</div>

{{-- ===== TAB CONTENT ===== --}}
<div class="tab-content">

    {{-- ---- ONGLET RÉSUMÉ ---- --}}
    <div class="tab-pane active" id="resume">

        {{-- Carte Pronostic COTA --}}
        <div class="pred-card {{ $prediction->is_premium ? 'pred-card--premium' : '' }}">
            <div class="pred-card__header">
                <div class="pred-card__header-left">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Pronostic COTA</span>
                </div>
                @if($prediction->is_premium ?? false)
                    <span class="pred-card__premium-badge">⭐ PREMIUM</span>
                @endif
            </div>

            <div class="pred-card__body">
                {{-- Pari principal --}}
                <div class="pred-card__bet">
                    <div class="pred-card__bet-info">
                        <span class="pred-card__bet-type">{{ $prediction->bet_type ?? 'Résultat' }}</span>
                        <span class="pred-card__bet-value">{{ $prediction->prediction ?? 'N/D' }}</span>
                    </div>
                    <div class="pred-card__odds">
                        <span class="pred-card__odds-label">Cote</span>
                        <span class="pred-card__odds-value">{{ number_format($prediction->odds ?? 0, 2) }}</span>
                    </div>
                </div>

                {{-- Confiance --}}
                <div class="pred-card__confidence">
                    <div class="pred-card__stars">
                        @for($i = 1; $i <= 4; $i++)
                            <i class="bi bi-star-fill {{ $i <= ($prediction->confidence_stars ?? 0) ? 'pred-card__star--active' : 'pred-card__star--empty' }}"></i>
                        @endfor
                    </div>
                    <div class="pred-card__score-bar-wrap">
                        <div class="pred-card__score-bar">
                            <div class="pred-card__score-bar-fill"
                                 style="--target: {{ $prediction->total_score ?? $prediction->confidence ?? 0 }}%">
                            </div>
                        </div>
                        <span class="pred-card__score-label">
                            {{ $prediction->total_score ?? $prediction->confidence ?? 0 }}/100
                        </span>
                    </div>
                </div>

                {{-- Résultat si terminé --}}
                @if(in_array($prediction->status ?? '', ['won', 'lost']))
                    <div class="pred-card__result pred-card__result--{{ $prediction->status }}">
                        <i class="bi bi-{{ $prediction->status === 'won' ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                        <span>Pronostic {{ $prediction->status === 'won' ? 'GAGNÉ !' : 'PERDU' }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Analyse --}}
        @php
            $analysis = is_string($prediction->analysis_details ?? null)
                ? json_decode($prediction->analysis_details, true)
                : ($prediction->analysis_details ?? null);
        @endphp
        @if(!empty($analysis['reasoning']))
            <div class="detail-section">
                <div class="detail-section__header">
                    <i class="bi bi-chat-left-quote-fill"></i>
                    <span>Analyse</span>
                </div>
                <div class="detail-section__body">
                    <p class="detail-section__text">{{ $analysis['reasoning'] }}</p>
                </div>
            </div>
        @endif

        {{-- Score breakdown animé --}}
        <div class="detail-section">
            <div class="detail-section__header">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Critères d'analyse</span>
                <span class="detail-section__badge">{{ $prediction->total_score ?? 0 }}/100</span>
            </div>
            <div class="detail-section__body">
                @php
                    $criteria = [
                        ['label' => 'Forme récente',    'icon' => 'bi-arrow-up-circle',   'value' => $prediction->score_form     ?? 0, 'max' => 25, 'color' => '#3B82F6'],
                        ['label' => 'Face à face',      'icon' => 'bi-arrow-left-right',   'value' => $prediction->score_h2h      ?? 0, 'max' => 20, 'color' => '#8B5CF6'],
                        ['label' => 'Dom / Ext',        'icon' => 'bi-house-fill',          'value' => $prediction->score_home_away ?? 0, 'max' => 15, 'color' => '#10B981'],
                        ['label' => 'Classement',       'icon' => 'bi-trophy-fill',         'value' => $prediction->score_league   ?? 0, 'max' => 12, 'color' => '#F59E0B'],
                        ['label' => 'Buts',             'icon' => 'bi-bullseye',            'value' => $prediction->score_goals    ?? 0, 'max' => 10, 'color' => '#EF4444'],
                        ['label' => 'Tirs cadrés',      'icon' => 'bi-cursor-fill',         'value' => $prediction->score_shots    ?? 0, 'max' => 3,  'color' => '#06B6D4'],
                        ['label' => 'Forme physique',   'icon' => 'bi-person-fill',         'value' => $prediction->score_physical ?? 0, 'max' => 2,  'color' => '#84CC16'],
                    ];
                @endphp

                @foreach($criteria as $c)
                    @php $pct = $c['max'] > 0 ? round(($c['value'] / $c['max']) * 100) : 0; @endphp
                    <div class="crit-row">
                        <div class="crit-row__label">
                            <i class="bi {{ $c['icon'] }}" style="color: {{ $c['color'] }}; font-size:0.875rem;"></i>
                            <span>{{ $c['label'] }}</span>
                        </div>
                        <div class="crit-row__bar-wrap">
                            <div class="crit-row__bar">
                                <div class="crit-row__bar-fill"
                                     style="--w: {{ $pct }}%; --color: {{ $c['color'] }};"></div>
                            </div>
                        </div>
                        <span class="crit-row__score">
                            {{ number_format($c['value'], 1) }}<span class="crit-row__max">/{{ $c['max'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="quick-actions">
            <a href="#" class="quick-action">
                <i class="bi bi-people-fill"></i>
                <span>Stats équipes</span>
            </a>
            <a href="#" class="quick-action">
                <i class="bi bi-trophy-fill"></i>
                <span>Tournoi</span>
            </a>
            <a href="#" class="quick-action">
                <i class="bi bi-list-ol"></i>
                <span>Classements</span>
            </a>
            <a href="#" class="quick-action">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Meilleurs joueurs</span>
            </a>
        </div>

    </div>{{-- /résumé --}}

    {{-- ---- ONGLET H2H ---- --}}
    <div class="tab-pane" id="h2h" style="display:none;">
        <div class="detail-section" style="margin-top:0;">
            <div class="detail-section__header">
                <i class="bi bi-arrow-left-right"></i>
                <span>Confrontations directes</span>
            </div>

            {{-- Sélecteur équipe --}}
            <div class="h2h-tabs">
                <button class="h2h-tabs__btn active" data-h2h="home">{{ $prediction->home_team }}</button>
                <button class="h2h-tabs__btn" data-h2h="away">{{ $prediction->away_team }}</button>
            </div>

            @php
                $fakeMatches = [
                    ['home' => 'Gabon',         'away' => $prediction->home_team, 'hs' => 2, 'as' => 3, 'r' => 'W', 'date' => '15 Mar'],
                    ['home' => $prediction->home_team, 'away' => 'Cameroun',      'hs' => 1, 'as' => 1, 'r' => 'D', 'date' => '01 Mar'],
                    ['home' => $prediction->home_team, 'away' => 'Mozambique',    'hs' => 1, 'as' => 0, 'r' => 'W', 'date' => '20 Fév'],
                    ['home' => 'Oman',          'away' => $prediction->home_team, 'hs' => 0, 'as' => 2, 'r' => 'W', 'date' => '10 Fév'],
                    ['home' => 'Arabie S.',     'away' => $prediction->home_team, 'hs' => 1, 'as' => 2, 'r' => 'L', 'date' => '02 Fév'],
                ];
                $colors = ['W' => '#10B981', 'D' => '#F59E0B', 'L' => '#EF4444'];
                $labels = ['W' => 'V', 'D' => 'N', 'L' => 'D'];
            @endphp

            <div class="h2h-list">
                @foreach($fakeMatches as $m)
                    <div class="h2h-row">
                        <span class="h2h-row__date">{{ $m['date'] }}</span>
                        <div class="h2h-row__match">
                            <span class="h2h-row__team h2h-row__team--home">{{ $m['home'] }}</span>
                            <span class="h2h-row__score">{{ $m['hs'] }} – {{ $m['as'] }}</span>
                            <span class="h2h-row__team h2h-row__team--away">{{ $m['away'] }}</span>
                        </div>
                        <span class="h2h-row__result" style="background: {{ $colors[$m['r']] }}20; color: {{ $colors[$m['r']] }};">
                            {{ $labels[$m['r']] }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Résumé W/D/L --}}
            <div class="h2h-summary">
                <div class="h2h-summary__item" style="color: #10B981;">
                    <span class="h2h-summary__count">3</span>
                    <span class="h2h-summary__label">Victoires</span>
                </div>
                <div class="h2h-summary__item" style="color: #F59E0B;">
                    <span class="h2h-summary__count">1</span>
                    <span class="h2h-summary__label">Nuls</span>
                </div>
                <div class="h2h-summary__item" style="color: #EF4444;">
                    <span class="h2h-summary__count">1</span>
                    <span class="h2h-summary__label">Défaites</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ---- ONGLET CLASSEMENTS ---- --}}
    <div class="tab-pane" id="classements" style="display:none;">
        <div class="detail-section" style="margin-top:0;">
            <div class="detail-section__header">
                <i class="bi bi-list-ol"></i>
                <span>Classement — {{ $prediction->competition ?? 'Compétition' }}</span>
            </div>
            <div class="detail-section__body">
                <div class="empty-state" style="padding: 32px 16px;">
                    <div class="empty-state__icon">
                        <i class="bi bi-trophy" style="font-size:1.5rem;"></i>
                    </div>
                    <p class="empty-state__text">Classement non disponible pour cette compétition.</p>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /tab-content --}}
</div>{{-- /match-detail-main --}}

{{-- ===== COLONNE DROITE DESKTOP (sticky) ===== --}}
<aside class="match-detail-aside">

    {{-- Carte pronostic collante --}}
    <div class="aside-pred {{ $prediction->is_premium ?? false ? 'aside-pred--premium' : '' }}">
        <div class="aside-pred__header">
            <i class="bi bi-lightning-charge-fill"></i>
            <span>Pronostic COTA</span>
            @if($prediction->is_premium ?? false)
                <span class="aside-pred__premium">⭐ PREMIUM</span>
            @endif
        </div>

        <div class="aside-pred__body">
            <div class="aside-pred__bet-type">{{ $prediction->bet_type ?? 'Résultat' }}</div>
            <div class="aside-pred__pick">{{ $prediction->prediction ?? 'N/D' }}</div>
            <div class="aside-pred__odds">
                Cote <strong>{{ number_format($prediction->odds ?? 0, 2) }}</strong>
            </div>

            <div class="aside-pred__stars">
                @for($i = 1; $i <= 4; $i++)
                    <i class="bi bi-star-fill" style="color: {{ $i <= ($prediction->confidence_stars ?? 0) ? '#F59E0B' : '#30363D' }};"></i>
                @endfor
                <span>{{ $prediction->total_score ?? 0 }}/100</span>
            </div>

            @if(!in_array($prediction->status ?? '', ['finished', 'won', 'lost', 'cancelled']))
                <a href="#" class="aside-pred__cta">
                    <i class="bi bi-plus-lg"></i>
                    Ajouter au coupon
                </a>
            @endif
        </div>
    </div>

    {{-- Score breakdown compact --}}
    <div class="aside-scores">
        <div class="aside-scores__title">Critères</div>
        @foreach($criteria as $c)
            @php $pct = $c['max'] > 0 ? round(($c['value'] / $c['max']) * 100) : 0; @endphp
            <div class="aside-scores__row">
                <span class="aside-scores__label">{{ $c['label'] }}</span>
                <div class="aside-scores__bar">
                    <div class="aside-scores__fill"
                         style="--w: {{ $pct }}%; --color: {{ $c['color'] }};"></div>
                </div>
                <span class="aside-scores__val">{{ $pct }}%</span>
            </div>
        @endforeach
    </div>

</aside>

</div>{{-- /match-detail-layout --}}

{{-- Bouton flottant mobile (hors desktop) --}}
@if(!in_array($prediction->status ?? '', ['finished', 'won', 'lost', 'cancelled']))
    <div class="float-cta">
        <a href="#" class="float-cta__btn">
            <i class="bi bi-plus-lg"></i>
            Ajouter au coupon
        </a>
    </div>
@endif

@endsection

@push('scripts')
<script>
// ---- Tabs ----
document.querySelectorAll('.cota-tabs__item').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.cota-tabs__item').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        this.classList.add('active');
        document.getElementById(this.dataset.tab).style.display = 'block';
    });
});

// ---- H2H team switch ----
document.querySelectorAll('.h2h-tabs__btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.h2h-tabs__btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

// ---- Favori ----
function toggleFavorite(id) {
    const btn = document.getElementById('favBtn');
    const icon = btn.querySelector('i');
    icon.classList.toggle('bi-star');
    icon.classList.toggle('bi-star-fill');
    icon.style.color = icon.classList.contains('bi-star-fill') ? 'var(--cota-favorite)' : '';
}

// ---- Animation barres de score ----
// Les barres utilisent CSS animations (--w variable), elles s'animent toutes seules.
// Mais on déclenche l'animation seulement quand visible (IntersectionObserver)
const bars = document.querySelectorAll('.crit-row__bar-fill, .aside-scores__fill, .pred-card__score-bar-fill');
if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('animated');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.2 });
    bars.forEach(b => obs.observe(b));
} else {
    bars.forEach(b => b.classList.add('animated'));
}
</script>
@endpush
