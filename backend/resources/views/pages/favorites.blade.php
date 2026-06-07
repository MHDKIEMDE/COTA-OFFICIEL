@extends('layouts.app')

@php $hideDate = true; @endphp

@section('page_title', 'Favoris')

@section('content')
<style>
    .fav-tabs {
        display: flex;
        gap: 6px;
        padding: 10px 16px;
        border-bottom: 1px solid var(--line2);
    }
    .fav-tab {
        flex: 1;
        text-align: center;
        padding: 8px 0;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 12px; font-weight: 700;
        color: var(--dim);
        border-bottom: 2px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: color .15s, border-color .15s;
    }
    .fav-tab.active { color: var(--acc); border-color: var(--acc); }
    .fav-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line2);
        text-decoration: none;
        color: inherit;
        transition: background .1s;
    }
    .fav-row:hover { background: var(--bg2); }
    .fav-row__logo {
        width: 40px; height: 40px;
        border-radius: 8px;
        object-fit: contain;
        background: var(--bg3);
        flex-shrink: 0;
    }
    .fav-row__logo--placeholder {
        display: flex; align-items: center; justify-content: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px; font-weight: 800;
        color: var(--dim);
        border: 1px solid var(--line);
    }
    .fav-row__info { flex: 1; min-width: 0; }
    .fav-row__name {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700;
        color: var(--ink);
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .fav-row__sub {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11px;
        color: var(--dim);
        margin-top: 2px;
    }
    .fav-row__action {
        color: var(--acc);
        font-size: 16px;
    }
    .fav-comp-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line2);
        cursor: pointer;
        transition: background .1s;
    }
    .fav-comp-item:hover { background: var(--bg2); }
    .fav-comp-item__flag { font-size: 22px; }
    .fav-comp-item__name {
        flex: 1;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700;
        color: var(--ink);
    }
    .fav-comp-item__country {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 10px;
        color: var(--dim);
        margin-top: 2px;
    }
    .fav-comp-follow {
        padding: 6px 14px;
        background: var(--bg3);
        border: 1px solid var(--line);
        border-radius: 20px;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11px; font-weight: 700;
        color: var(--ink);
        cursor: pointer;
    }
    .fav-comp-follow.followed { background: rgba(232,255,54,.10); border-color: rgba(232,255,54,.3); color: var(--acc); }
</style>

<div class="fav-tabs">
    <a href="#" class="fav-tab active" data-tab="matches" onclick="switchTab('matches',this);return false;">Matchs</a>
    <a href="#" class="fav-tab" data-tab="teams" onclick="switchTab('teams',this);return false;">Équipes</a>
    <a href="#" class="fav-tab" data-tab="comps" onclick="switchTab('comps',this);return false;">Compétitions</a>
</div>

@auth
    {{-- Onglet Matchs --}}
    <div id="tab-matches">
        @forelse($favoriteMatches ?? [] as $prediction)
            <a href="{{ route('predictions.show', $prediction->id) }}" class="fav-row">
                <div class="fav-row__logo fav-row__logo--placeholder">{{ strtoupper(substr($prediction->home_team ?? '?', 0, 3)) }}</div>
                <div class="fav-row__info">
                    <div class="fav-row__name">{{ $prediction->home_team ?? '—' }} vs {{ $prediction->away_team ?? '—' }}</div>
                    <div class="fav-row__sub">{{ $prediction->competition ?? '—' }} · {{ isset($prediction->match_date) ? \Carbon\Carbon::parse($prediction->match_date)->format('d/m H:i') : '—' }}</div>
                </div>
                <i class="bi bi-star-fill fav-row__action"></i>
            </a>
        @empty
            <div class="c-empty" style="min-height:45vh;">
                <div class="c-empty__icon"><i class="bi bi-star"></i></div>
                <div class="c-empty__title">Aucun match favori</div>
                <div class="c-empty__sub">Appuie sur ⭐ sur un pronostic pour le retrouver ici.</div>
                <a href="{{ route('home') }}" style="margin-top:8px;padding:10px 20px;background:var(--acc);border-radius:8px;font-family:'Archivo',sans-serif;font-size:13px;font-weight:900;color:var(--bg);text-decoration:none;">
                    Explorer →
                </a>
            </div>
        @endforelse
    </div>

    {{-- Onglet Équipes --}}
    <div id="tab-teams" style="display:none;">
        @forelse($favoriteTeams ?? [] as $team)
            <div class="fav-row">
                @if($team->logo ?? null)
                    <img src="{{ $team->logo }}" alt="" class="fav-row__logo">
                @else
                    <div class="fav-row__logo fav-row__logo--placeholder">{{ strtoupper(substr($team->name ?? '?', 0, 3)) }}</div>
                @endif
                <div class="fav-row__info">
                    <div class="fav-row__name">{{ $team->name ?? '—' }}</div>
                    <div class="fav-row__sub">{{ $team->country ?? '—' }}</div>
                </div>
                <i class="bi bi-star-fill fav-row__action"></i>
            </div>
        @empty
            <div class="c-empty" style="min-height:45vh;">
                <div class="c-empty__icon"><i class="bi bi-people"></i></div>
                <div class="c-empty__title">Aucune équipe</div>
                <div class="c-empty__sub">Suis tes équipes préférées pour suivre leurs résultats.</div>
            </div>
        @endforelse
    </div>

    {{-- Onglet Compétitions --}}
    <div id="tab-comps" style="display:none;">
        @php
            $popularComps = [
                ['icon'=>'🏴󠁧󠁢󠁥󠁮󠁧󠁿','name'=>'Premier League','country'=>'Angleterre'],
                ['icon'=>'🇪🇸','name'=>'La Liga','country'=>'Espagne'],
                ['icon'=>'🇮🇹','name'=>'Serie A','country'=>'Italie'],
                ['icon'=>'🇫🇷','name'=>'Ligue 1','country'=>'France'],
                ['icon'=>'🇩🇪','name'=>'Bundesliga','country'=>'Allemagne'],
                ['icon'=>'🏆','name'=>'Champions League','country'=>'Europe'],
                ['icon'=>'🟠','name'=>'Europa League','country'=>'Europe'],
                ['icon'=>'🌍','name'=>'CAN 2026','country'=>'Afrique'],
            ];
        @endphp
        <p class="c-section">Suggestions</p>
        @foreach($popularComps as $comp)
            <div class="fav-comp-item">
                <span class="fav-comp-item__flag">{{ $comp['icon'] }}</span>
                <div>
                    <div class="fav-comp-item__name">{{ $comp['name'] }}</div>
                    <div class="fav-comp-item__country">{{ $comp['country'] }}</div>
                </div>
                <button class="fav-comp-follow" onclick="toggleFollow(this)">+ Suivre</button>
            </div>
        @endforeach
    </div>

@else
    <div class="c-empty" style="min-height:65vh;">
        <div class="c-empty__icon"><i class="bi bi-star"></i></div>
        <div class="c-empty__title">Tes favoris</div>
        <div class="c-empty__sub">Connecte-toi pour sauvegarder matchs, équipes et compétitions.</div>
        <a href="{{ route('login') }}" style="margin-top:12px;padding:12px 28px;background:var(--acc);border-radius:10px;font-family:'Archivo',sans-serif;font-size:14px;font-weight:900;color:var(--bg);text-decoration:none;">
            Se connecter →
        </a>
    </div>
@endauth

<div style="height:16px;"></div>
@endsection

@push('scripts')
<script>
function switchTab(tab, el) {
    document.querySelectorAll('.fav-tab').forEach(t => t.classList.remove('active'));
    ['matches','teams','comps'].forEach(id => {
        const pane = document.getElementById('tab-' + id);
        if (pane) pane.style.display = id === tab ? 'block' : 'none';
    });
    el.classList.add('active');
}
function toggleFollow(btn) {
    const on = btn.classList.toggle('followed');
    btn.textContent = on ? '✓ Suivi' : '+ Suivre';
}
</script>
@endpush
