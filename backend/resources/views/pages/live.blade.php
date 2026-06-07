<x-web-layout pageTitle="Live — COTA">
<style>
    @keyframes livePulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(255,91,58,.5)} 50%{opacity:.7;box-shadow:0 0 0 6px rgba(255,91,58,0)} }
    .live-dot { width:8px;height:8px;background:var(--loss);border-radius:50%;animation:livePulse 2s infinite;flex-shrink:0; }
    .match-live { display:block;background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:16px;text-decoration:none;color:inherit;transition:border-color .15s;margin-bottom:14px; }
    .match-live:hover { border-color:var(--loss); }
    .match-live--active { border-color:rgba(255,91,58,.35); }
    .match-teams { display:flex;align-items:center;gap:10px; }
    .match-team { flex:1;display:flex;flex-direction:column;align-items:center;gap:6px; }
    .match-team__logo { width:42px;height:42px;border-radius:8px;object-fit:contain;background:var(--bg3); }
    .match-team__name { font-family:var(--ui);font-size:13px;font-weight:600;color:var(--ink);text-align:center;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .match-score { display:flex;flex-direction:column;align-items:center;gap:4px;min-width:72px; }
    .match-score__nums { font-family:var(--title);font-size:32px;color:var(--ink);letter-spacing:-1px;line-height:1; }
    .match-score__time { font-family:var(--mono);font-size:12px;font-weight:700;color:var(--loss); }
    .match-score__ht { font-family:var(--mono);font-size:10px;color:#f5a623; }
    .match-meta { display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid var(--line2); }
    .match-meta__comp { font-family:var(--ui);font-size:11px;color:var(--dim);display:flex;align-items:center;gap:5px; }
    .match-meta__comp img { width:14px;height:14px;border-radius:2px; }
    .live-badge { font-family:var(--mono);font-size:9px;font-weight:700;color:#fff;background:var(--loss);padding:2px 8px;border-radius:4px;letter-spacing:.5px; }
    .ht-badge { font-family:var(--mono);font-size:9px;font-weight:700;color:var(--bg);background:#f5a623;padding:2px 8px;border-radius:4px; }
</style>

  <div class="wd-topbar">
    <div>
      <div class="wd-date">EN DIRECT</div>
      <h1 class="wd-h1">Matchs live</h1>
    </div>
    <div class="wd-topactions">
      <span class="wd-badge" style="color:var(--loss);background:rgba(255,91,58,.16)">
        <span class="wd-livedot"></span>
        {{ $liveCount ?? count($liveMatches ?? []) }} en direct
      </span>
    </div>
  </div>

  @forelse($liveMatches ?? [] as $match)
    @php $isHT = strtolower($match->match_status ?? '') === 'halftime'; $elapsed = $match->elapsed_time ?? null; @endphp
    <a href="#" class="match-live {{ !$isHT ? 'match-live--active' : '' }}">
      <div class="match-teams">
        <div class="match-team">
          @if($match->home_team_logo)
            <img src="{{ $match->home_team_logo }}" alt="{{ $match->home_team }}" class="match-team__logo">
          @else
            <div class="match-team__logo" style="display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:10px;font-weight:800;color:var(--dim);">{{ strtoupper(substr($match->home_team ?? '?', 0, 3)) }}</div>
          @endif
          <span class="match-team__name">{{ $match->home_team }}</span>
        </div>
        <div class="match-score">
          <div class="match-score__nums">{{ $match->home_score ?? 0 }}&nbsp;-&nbsp;{{ $match->away_score ?? 0 }}</div>
          @if($isHT)<span class="match-score__ht">MI-TEMPS</span>
          @elseif($elapsed)<span class="match-score__time">{{ $elapsed }}'</span>@endif
        </div>
        <div class="match-team">
          @if($match->away_team_logo)
            <img src="{{ $match->away_team_logo }}" alt="{{ $match->away_team }}" class="match-team__logo">
          @else
            <div class="match-team__logo" style="display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:10px;font-weight:800;color:var(--dim);">{{ strtoupper(substr($match->away_team ?? '?', 0, 3)) }}</div>
          @endif
          <span class="match-team__name">{{ $match->away_team }}</span>
        </div>
      </div>
      <div class="match-meta">
        <div class="match-meta__comp">
          @if($match->competition_logo)<img src="{{ $match->competition_logo }}" alt="">@endif
          {{ $match->competition ?? '—' }}
        </div>
        @if($isHT)<span class="ht-badge">MI-TEMPS</span>
        @else<span class="live-badge">● LIVE {{ $elapsed ? $elapsed."'" : '' }}</span>@endif
      </div>
    </a>
  @empty
    <div style="text-align:center;padding:64px 32px;color:var(--dim)">
      <div style="font-family:var(--title);font-size:20px;color:var(--ink);margin-bottom:10px">Aucun match en direct</div>
      <div style="font-size:13.5px">Aucun match en cours pour l'instant. Reviens bientôt.</div>
      <a href="{{ route('home') }}" style="display:inline-block;margin-top:16px;padding:10px 22px;background:var(--bg2);border:1px solid var(--line);border-radius:10px;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink);text-decoration:none;">Voir les pronostics →</a>
    </div>
  @endforelse

</x-web-layout>
