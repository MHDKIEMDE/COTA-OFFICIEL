<x-web-layout pageTitle="Historique — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOTRE PARCOURS</div>
      <h1 class="wd-h1">Historique</h1>
      <p class="wd-desc">Toutes vos prédictions suivies, gagnées comme perdues — en toute transparence.</p>
    </div>
    <div class="wd-topactions">
      <button class="wd-ghost-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 5h18l-7 8v6l-4-2v-4z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Filtrer
      </button>
    </div>
  </div>

  {{-- KPIs --}}
  @php
    $kpis = [
      ['val'=>$stats['won'],'label'=>'GAGNÉES','icon'=>'<path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>','tone'=>'lime'],
      ['val'=>$stats['lost'],'label'=>'PERDUES','icon'=>'<path d="M12 19V5m0 0-6 6m6-6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>','tone'=>'cool'],
      ['val'=>$stats['win_rate'].'%','label'=>'RÉUSSITE','icon'=>'<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none"/>','tone'=>'lime'],
      ['val'=>$stats['pending'],'label'=>'EN ATTENTE','icon'=>'<rect x="3" y="6" width="18" height="13" rx="2.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="13.5" r="1.3" fill="currentColor" stroke="none"/>','tone'=>'cool'],
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

  <div class="wd-panel">
    <div class="wd-panelhead">
      <div>
        <div class="wd-panel-title">Toutes mes prédictions</div>
        <div class="wd-panel-sub">ORDRE ANTÉCHRONOLOGIQUE</div>
      </div>
    </div>
    <div class="wd-table">
      <div class="wd-tr wd-thead">
        <div class="wd-cell wd-c-match">Match</div>
        <div class="wd-cell wd-c-pick">Sélection</div>
        <div class="wd-cell wd-c-odds">Cote</div>
        <div class="wd-cell wd-c-stat">Statut</div>
        <div class="wd-cell wd-c-gain">Résultat</div>
      </div>
      @php
        $statMap = ['won'=>['label'=>'Gagné','color'=>'var(--win)','bg'=>'rgba(61,220,145,.12)'],'lost'=>['label'=>'Perdu','color'=>'var(--loss)','bg'=>'rgba(255,91,58,.12)'],'pending'=>['label'=>'En attente','color'=>'var(--accent)','bg'=>'rgba(232,255,54,.1)'],'live'=>['label'=>'Live','color'=>'var(--loss)','bg'=>'rgba(255,91,58,.12)']];
      @endphp
      @forelse($predictions as $pred)
      @php $st = $statMap[$pred->status] ?? $statMap['pending']; @endphp
      <div class="wd-tr">
        <div class="wd-cell wd-c-match">
          <div class="wd-thumb">
            <span style="font-family:var(--mono);font-size:7px;color:var(--dim)">{{ strtoupper(substr($pred->home_team??'?',0,3)) }}</span>
            <span style="font-family:var(--mono);font-size:7px;color:var(--dim2)">·</span>
            <span style="font-family:var(--mono);font-size:7px;color:var(--dim)">{{ strtoupper(substr($pred->away_team??'?',0,3)) }}</span>
          </div>
          <div style="min-width:0">
            <div style="font-family:var(--title);font-size:14px;color:var(--ink);letter-spacing:-.01em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ Str::limit(($pred->home_team??'?').' – '.($pred->away_team??'?'),26) }}</div>
            <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-top:3px">{{ $pred->match_date ? \Carbon\Carbon::parse($pred->match_date)->format('d M Y') : '—' }}</div>
          </div>
        </div>
        <div class="wd-cell wd-c-pick" style="font-size:13px;color:var(--ink2)">{{ Str::limit($pred->prediction_type??$pred->bet_type??'—',22) }}</div>
        <div class="wd-cell wd-c-odds" style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--ink)">{{ $pred->odds ? '@'.$pred->odds : '—' }}</div>
        <div class="wd-cell wd-c-stat">
          <span class="wd-badge" style="color:{{ $st['color'] }};background:{{ $st['bg'] }}">
            @if($pred->status==='live')<span class="wd-livedot"></span>@endif
            {{ $st['label'] }}
          </span>
        </div>
        <div class="wd-cell wd-c-gain" style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $pred->status==='won'?'var(--win)':($pred->status==='lost'?'var(--loss)':'var(--dim)') }}">—</div>
      </div>
      @empty
      <div style="padding:24px 22px;color:var(--dim);font-size:13.5px;text-align:center">Aucun résultat dans votre historique.</div>
      @endforelse
    </div>
  </div>

</x-web-layout>
