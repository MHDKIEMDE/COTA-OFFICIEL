@php
    $winRate   = $stats['win_rate']   ?? 0;
    $won       = $stats['won']        ?? 0;
    $lost      = $stats['lost']       ?? 0;
    $pending   = $stats['pending']    ?? 0;
    $total     = $stats['total_predictions'] ?? ($won + $lost + $pending);
    $roi       = $stats['roi']        ?? 0;
    $avgOdds   = $stats['avg_odds']   ?? 0;
    $streak    = $stats['streak']     ?? ['count' => 0, 'type' => 'none'];
    $dash      = ($winRate / 100) * 100.53;
@endphp

<x-web-layout pageTitle="Statistiques — COTA">
<style>
    .s-ring-wrap { display:flex;flex-direction:column;align-items:center;background:var(--bg2);border:1px solid var(--line);border-radius:16px;padding:28px 20px 22px;margin-bottom:22px; }
    .s-ring { position:relative;width:120px;height:120px;margin-bottom:18px; }
    .s-ring svg { transform:rotate(-90deg); }
    .s-ring__center { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center; }
    .s-ring__pct { font-family:var(--title);font-size:28px;color:var(--ink);line-height:1; }
    .s-ring__label { font-family:var(--mono);font-size:8px;color:var(--dim);letter-spacing:1px;margin-top:3px; }
    .s-ring__row { display:flex;align-items:center;justify-content:center;gap:28px; }
    .s-ring__stat { text-align:center; }
    .s-ring__stat-val { font-family:var(--title);font-size:24px;line-height:1; }
    .s-ring__stat-label { font-family:var(--ui);font-size:10px;color:var(--dim);margin-top:3px; }
    .s-ring__div { width:1px;height:32px;background:var(--line); }
    .s-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px; }
    .s-card { background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:16px; }
    .s-card__icon { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;margin-bottom:10px; }
    .s-card__label { font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.1em;margin-bottom:4px;text-transform:uppercase; }
    .s-card__val { font-family:var(--title);font-size:26px;color:var(--ink);line-height:1; }
    .s-comp-row { display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid var(--line2); }
    .s-comp-row:last-child { border-bottom:none; }
    .s-comp-bar-wrap { flex:1; }
    .s-comp-name { font-family:var(--ui);font-size:13px;font-weight:600;color:var(--ink);margin-bottom:6px; }
    .s-comp-bar-bg { height:5px;background:var(--line);border-radius:2px;overflow:hidden; }
    .s-comp-bar-fill { height:100%;border-radius:2px;background:var(--win); }
    .s-comp-pct { font-family:var(--mono);font-size:13px;font-weight:700;min-width:38px;text-align:right; }
    @media(max-width:960px){ .s-grid { grid-template-columns:repeat(2,1fr); } }
</style>

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOS PERFORMANCES</div>
      <h1 class="wd-h1">Statistiques</h1>
    </div>
  </div>

  {{-- Ring --}}
  <div class="s-ring-wrap">
    <div class="s-ring">
      <svg viewBox="0 0 36 36" width="120" height="120">
        <path d="M18 2.08 a 15.92 15.92 0 0 1 0 31.84 a 15.92 15.92 0 0 1 0 -31.84" fill="none" stroke="var(--line)" stroke-width="3"/>
        <path d="M18 2.08 a 15.92 15.92 0 0 1 0 31.84 a 15.92 15.92 0 0 1 0 -31.84" fill="none" stroke="var(--win)" stroke-width="3" stroke-dasharray="{{ $dash }}, 100.53" stroke-linecap="round"/>
      </svg>
      <div class="s-ring__center">
        <span class="s-ring__pct">{{ $winRate }}%</span>
        <span class="s-ring__label">RÉUSSITE</span>
      </div>
    </div>
    <div class="s-ring__row">
      <div class="s-ring__stat"><div class="s-ring__stat-val" style="color:var(--win);">{{ $won }}</div><div class="s-ring__stat-label">Gagnés</div></div>
      <div class="s-ring__div"></div>
      <div class="s-ring__stat"><div class="s-ring__stat-val" style="color:var(--loss);">{{ $lost }}</div><div class="s-ring__stat-label">Perdus</div></div>
      <div class="s-ring__div"></div>
      <div class="s-ring__stat"><div class="s-ring__stat-val" style="color:var(--dim);">{{ $pending }}</div><div class="s-ring__stat-label">En cours</div></div>
    </div>
  </div>

  {{-- Métriques --}}
  <div class="s-grid">
    <div class="s-card">
      <div class="s-card__icon" style="background:rgba(232,255,54,.10);color:var(--accent);">📈</div>
      <div class="s-card__label">ROI</div>
      <div class="s-card__val" style="color:{{ $roi >= 0 ? 'var(--win)' : 'var(--loss)' }};">{{ $roi >= 0 ? '+' : '' }}{{ $roi }}%</div>
    </div>
    <div class="s-card">
      <div class="s-card__icon" style="background:rgba(61,220,145,.10);color:var(--win);">🎯</div>
      <div class="s-card__label">Cote moy.</div>
      <div class="s-card__val">{{ number_format($avgOdds, 2) }}</div>
    </div>
    <div class="s-card">
      <div class="s-card__icon" style="background:rgba(255,91,58,.10);color:var(--loss);">🔥</div>
      <div class="s-card__label">Série</div>
      <div class="s-card__val" style="color:{{ ($streak['type'] ?? '') === 'win' ? 'var(--win)' : 'var(--loss)' }};">{{ $streak['count'] ?? 0 }}{{ ($streak['type'] ?? '') === 'win' ? 'W' : 'L' }}</div>
    </div>
    <div class="s-card">
      <div class="s-card__icon" style="background:rgba(232,255,54,.10);color:var(--accent);">⚡</div>
      <div class="s-card__label">Total</div>
      <div class="s-card__val">{{ $total }}</div>
    </div>
  </div>

  {{-- Par compétition --}}
  @if(($byCompetition ?? collect())->isNotEmpty())
  <div class="wd-panel">
    <div class="wd-panelhead">
      <div class="wd-panel-title">Par compétition</div>
    </div>
    @foreach($byCompetition as $comp)
      @php $pct = $comp->win_rate ?? 0; $barColor = $pct >= 60 ? 'var(--win)' : ($pct >= 50 ? 'var(--accent)' : 'var(--loss)'); @endphp
      <div class="s-comp-row">
        <div class="s-comp-bar-wrap">
          <div class="s-comp-name">{{ $comp->name }}</div>
          <div class="s-comp-bar-bg"><div class="s-comp-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div></div>
        </div>
        <div class="s-comp-pct" style="color:{{ $barColor }};">{{ $pct }}%</div>
      </div>
    @endforeach
  </div>
  @endif

</x-web-layout>
