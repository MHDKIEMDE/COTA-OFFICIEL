<x-web-layout pageTitle="Historique — COTA">
<style>
    .h-stats { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px; }
    .h-stat { background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:14px;text-align:center; }
    .h-stat__val { font-family:var(--title);font-size:24px;line-height:1;color:var(--ink); }
    .h-stat__label { font-family:var(--mono);font-size:8px;color:var(--dim);letter-spacing:1px;text-transform:uppercase;margin-top:5px; }
    .h-tabs { display:flex;gap:6px;margin-bottom:18px;overflow-x:auto;scrollbar-width:none; }
    .h-tabs::-webkit-scrollbar { display:none; }
    .h-tab { flex-shrink:0;padding:6px 16px;border-radius:20px;font-family:var(--ui);font-size:12px;font-weight:700;color:var(--dim);background:var(--bg2);border:1px solid var(--line);text-decoration:none;transition:all .15s; }
    .h-tab.active { background:var(--accent);border-color:var(--accent);color:var(--bg); }
    .h-row { display:flex;align-items:center;gap:12px;padding:13px 18px;border-bottom:1px solid var(--line2);text-decoration:none;color:inherit;transition:background .1s; }
    .h-row:last-child { border-bottom:none; }
    .h-row:hover { background:var(--bg3); }
    .h-row__bar { width:3px;align-self:stretch;border-radius:2px;flex-shrink:0; }
    .h-row__logos { display:flex;flex-direction:column;align-items:center;gap:2px; }
    .h-row__logo { width:22px;height:22px;border-radius:4px;object-fit:contain;background:var(--bg3); }
    .h-row__logo--placeholder { display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:7px;font-weight:800;color:var(--dim);background:var(--bg3);border:1px solid var(--line); }
    .h-row__info { flex:1;min-width:0; }
    .h-row__match { font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .h-row__sub { font-family:var(--ui);font-size:11px;color:var(--dim);margin-top:2px; }
    .h-row__right { text-align:right;flex-shrink:0; }
    .h-row__date { font-family:var(--mono);font-size:10px;color:var(--dim); }
    .h-row__score { font-family:var(--mono);font-size:13px;font-weight:700;color:var(--ink);margin-top:3px; }
    .h-row__status { font-family:var(--mono);font-size:9px;font-weight:700;padding:2px 7px;border-radius:4px;margin-top:3px;display:inline-block; }
    @media(max-width:760px){ .h-stats { grid-template-columns:repeat(2,1fr); } }
</style>

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOTRE PARCOURS</div>
      <h1 class="wd-h1">Historique</h1>
    </div>
    <a href="{{ route('history') }}" class="wd-ghost-btn">Tout voir</a>
  </div>

  {{-- Stats --}}
  <div class="h-stats">
    <div class="h-stat"><div class="h-stat__val">{{ $stats['total'] ?? 0 }}</div><div class="h-stat__label">Total</div></div>
    <div class="h-stat"><div class="h-stat__val" style="color:var(--win);">{{ $stats['won'] ?? 0 }}</div><div class="h-stat__label">Gagnés</div></div>
    <div class="h-stat"><div class="h-stat__val" style="color:var(--loss);">{{ $stats['lost'] ?? 0 }}</div><div class="h-stat__label">Perdus</div></div>
    <div class="h-stat"><div class="h-stat__val" style="color:var(--accent);">{{ $stats['win_rate'] ?? 0 }}%</div><div class="h-stat__label">Taux</div></div>
  </div>

  {{-- Filtres --}}
  <div class="h-tabs">
    <a href="{{ route('history') }}" class="h-tab {{ !request('status') ? 'active' : '' }}">Tous</a>
    <a href="{{ route('history', ['status' => 'won']) }}"     class="h-tab {{ request('status') === 'won'     ? 'active' : '' }}">★ Gagnés</a>
    <a href="{{ route('history', ['status' => 'lost']) }}"    class="h-tab {{ request('status') === 'lost'    ? 'active' : '' }}">Perdus</a>
    <a href="{{ route('history', ['status' => 'pending']) }}" class="h-tab {{ request('status') === 'pending' ? 'active' : '' }}">En attente</a>
  </div>

  <div class="wd-panel">
    @forelse($predictions ?? [] as $prediction)
      @php
        $status    = $prediction->result ?? $prediction->status ?? 'pending';
        $barColor  = match($status) { 'won' => 'var(--win)', 'lost' => 'var(--loss)', default => 'var(--line)' };
        $badgeBg   = match($status) { 'won' => 'rgba(61,220,145,.15)', 'lost' => 'rgba(255,91,58,.12)', default => 'var(--bg3)' };
        $badgeColor = match($status) { 'won' => 'var(--win)', 'lost' => 'var(--loss)', default => 'var(--dim)' };
        $badgeLabel = match($status) { 'won' => '✓ WIN', 'lost' => '✗ LOSS', default => '⏳' };
      @endphp
      <a href="{{ isset($prediction->id) ? route('predictions.show', $prediction->id) : '#' }}" class="h-row">
        <div class="h-row__bar" style="background:{{ $barColor }};"></div>
        <div class="h-row__logos">
          @if($prediction->home_team_logo ?? null)<img src="{{ $prediction->home_team_logo }}" class="h-row__logo" alt="">
          @else<div class="h-row__logo h-row__logo--placeholder">{{ strtoupper(substr($prediction->home_team ?? '?', 0, 3)) }}</div>@endif
          @if($prediction->away_team_logo ?? null)<img src="{{ $prediction->away_team_logo }}" class="h-row__logo" alt="">
          @else<div class="h-row__logo h-row__logo--placeholder">{{ strtoupper(substr($prediction->away_team ?? '?', 0, 3)) }}</div>@endif
        </div>
        <div class="h-row__info">
          <div class="h-row__match">{{ $prediction->home_team ?? '—' }} vs {{ $prediction->away_team ?? '—' }}</div>
          <div class="h-row__sub">{{ $prediction->predicted_outcome ?? $prediction->prediction ?? '—' }}</div>
        </div>
        <div class="h-row__right">
          <div class="h-row__date">{{ isset($prediction->match_date) ? \Carbon\Carbon::parse($prediction->match_date)->format('d/m') : '—' }}</div>
          <div class="h-row__score">{{ $prediction->home_score ?? '-' }} : {{ $prediction->away_score ?? '-' }}</div>
          <span class="h-row__status" style="background:{{ $badgeBg }};color:{{ $badgeColor }};">{{ $badgeLabel }}</span>
        </div>
      </a>
    @empty
      <div style="text-align:center;padding:40px 32px;color:var(--dim)">
        <div style="font-family:var(--title);font-size:18px;color:var(--ink);margin-bottom:8px">Aucun historique</div>
        @auth<div style="font-size:13.5px">Tes pronostics terminés apparaîtront ici.</div>
        @else<div style="font-size:13.5px">Connecte-toi pour voir ton historique.</div>
        <a href="{{ route('login') }}" style="display:inline-block;margin-top:14px;padding:10px 22px;background:var(--accent);border-radius:10px;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--bg);text-decoration:none;">Se connecter →</a>@endauth
      </div>
    @endforelse
  </div>

</x-web-layout>
