<x-web-layout pageTitle="Statistiques — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOS PERFORMANCES</div>
      <h1 class="wd-h1">Statistiques</h1>
      <p class="wd-desc">Le détail de vos résultats : rendement dans le temps, par compétition et par type de pari.</p>
    </div>
  </div>

  {{-- KPIs --}}
  @php
    $kpis = [
      ['val'=>$stats['total_predictions'],'label'=>'PRÉDICTIONS','icon'=>'<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 10h18M7 14h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>','tone'=>'lime'],
      ['val'=>$stats['win_rate'].'%','label'=>'TAUX RÉUSSITE','icon'=>'<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none"/>','tone'=>'lime'],
      ['val'=>($stats['roi']??0).'%','label'=>'ROI CUMULÉ','icon'=>'<path d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>','tone'=>'cool'],
      ['val'=>isset($stats['streak']) ? $stats['streak']['count'] : '—','label'=>'SÉRIE EN COURS','icon'=>'<path d="M12 3s5 4 5 9a5 5 0 0 1-10 0c0-1.5.7-2.8 1.5-3.6C8.5 9 9 11 9 11s.5-5 3-8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>','tone'=>'cool'],
    ];
  @endphp
  <div class="wd-kpis">
    @foreach($kpis as $kpi)
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" style="color:{{ $kpi['tone']==='cool'?'var(--cool)':'var(--accent)' }}">{!! $kpi['icon'] !!}</svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $kpi['val'] }}</div>
        <div class="wd-kpi-label">{{ $kpi['label'] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="wd-cols">
    <div class="wd-maincol">

      {{-- Résumé --}}
      @if($stats['total_predictions'] > 0)
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Résumé</div>
            <div class="wd-panel-sub">TOUTES LES PRÉDICTIONS RÉSOLUES</div>
          </div>
          <span class="wd-pill" style="background:rgba(61,220,145,.12);color:var(--win)">▲ {{ $stats['win_rate'] }}% de réussite</span>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $total = max($stats['total_predictions'],1);
            $items = [
              ['label'=>'Prédictions gagnées','val'=>$stats['won'],'pct'=>round(($stats['won']/$total)*100),'color'=>'var(--win)'],
              ['label'=>'Prédictions perdues','val'=>$stats['lost'],'pct'=>round(($stats['lost']/$total)*100),'color'=>'var(--loss)'],
              ['label'=>'En attente de résultat','val'=>$stats['pending'],'pct'=>round(($stats['pending']/$total)*100),'color'=>'var(--dim)'],
            ];
          @endphp
          @foreach($items as $item)
          <div class="wd-sum-item">
            <div class="wd-sum-header">
              <span style="font-size:13px;color:var(--ink2)">{{ $item['label'] }}</span>
              <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $item['color'] }}">{{ $item['val'] }} ({{ $item['pct'] }}%)</span>
            </div>
            <div class="wd-sum-track"><div class="wd-sum-fill" style="width:{{ $item['pct'] }}%;background:{{ $item['color'] }}"></div></div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Par compétition --}}
      @if($byCompetition->isNotEmpty())
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Réussite par compétition</div>
        </div>
        <div class="wd-panel-body no-top">
          @foreach($byCompetition->sortByDesc('win_rate') as $comp)
          <div class="wd-statbar">
            <div class="wd-statbar-header">
              <span style="font-size:13.5px;color:var(--ink);font-weight:500">{{ $comp->name }}</span>
              <span style="font-family:var(--mono);font-size:12px;color:var(--ink2);white-space:nowrap">{{ $comp->win_rate }}% · {{ $comp->total }} paris</span>
            </div>
            <div class="wd-statbar-track"><div class="wd-statbar-fill" style="width:{{ $comp->win_rate }}%;background:var(--accent)"></div></div>
          </div>
          @endforeach
        </div>
      </div>
      @endif

    </div>
    <div class="wd-rail">

      {{-- Meilleure perf --}}
      @if($stats['best_competition'])
      <div class="wd-panel" style="padding:22px">
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-bottom:14px">MEILLEURE COMPÉTITION</div>
        <div style="font-family:var(--title);font-size:20px;color:var(--ink)">{{ $stats['best_competition']['name'] }}</div>
        <div style="font-family:var(--title);font-size:32px;color:var(--accent);letter-spacing:-.03em;margin-top:8px">{{ $stats['best_competition']['win_rate'] }}%</div>
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-top:6px">DE RÉUSSITE</div>
      </div>
      @endif

      @if($stats['best_bet_type'])
      <div class="wd-panel" style="padding:22px">
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-bottom:14px">MEILLEUR TYPE DE PARI</div>
        <div style="font-family:var(--title);font-size:18px;color:var(--ink)">{{ $stats['best_bet_type']['name'] }}</div>
        <div style="font-family:var(--title);font-size:32px;color:var(--cool);letter-spacing:-.03em;margin-top:8px">{{ $stats['best_bet_type']['win_rate'] }}%</div>
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-top:6px">DE RÉUSSITE</div>
      </div>
      @endif

      {{-- Conseil --}}
      <div class="wd-panel" style="padding:22px">
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-bottom:14px">CONSEIL COTA</div>
        <p style="font-size:13px;color:var(--ink2);line-height:1.6;margin:0">
          @if($stats['win_rate'] >= 60)
            Votre taux de réussite de <strong style="color:var(--ink)">{{ $stats['win_rate'] }}%</strong> est excellent. Continuez à suivre les picks à haute confiance (≥70%).
          @elseif($stats['win_rate'] >= 50)
            Votre taux de <strong style="color:var(--ink)">{{ $stats['win_rate'] }}%</strong> est dans la bonne direction. Concentrez-vous sur les prédictions 3–4 étoiles.
          @else
            Privilégiez les picks avec un score de confiance ≥ 70 pour améliorer votre rendement.
          @endif
        </p>
        @if($stats['win_rate'] > 0)
        <div style="margin-top:14px">
          <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-bottom:6px">DISCIPLINE</div>
          <div style="height:8px;background:var(--bg3);border-radius:4px;overflow:hidden">
            <div style="width:{{ min($stats['win_rate'],100) }}%;height:100%;background:var(--accent);border-radius:4px"></div>
          </div>
        </div>
        @endif
      </div>

    </div>
  </div>

</x-web-layout>
