<x-web-layout pageTitle="Dashboard — COTA">

  {{-- Topbar --}}
  <div class="wd-topbar">
    <div>
      <div class="wd-date">{{ strtoupper(now()->locale('fr')->isoFormat('ddd. D MMM YYYY')) }}</div>
      <h1 class="wd-h1">@auth Bon retour, {{ explode(' ', auth()->user()->name)[0] }}. @else Bienvenue sur COTA. @endauth</h1>
    </div>
    <div class="wd-topactions">
      @auth
      <button class="wd-iconbtn" title="Notifications" style="position:relative">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 19a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="wd-dot"></span>
      </button>
      @endauth
      <a href="{{ route('predictions.index') }}" class="wd-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Pronostics du jour
      </a>
    </div>
  </div>

  {{-- KPIs --}}
  @php
    $kpis = [
      ['value' => $stats['today_count'] ?? 0, 'label' => "PRONOSTICS AUJOURD'HUI", 'delta' => ($stats['premium_count'] ?? 0).' premium', 'up' => true, 'tone' => 'lime',
       'icon' => '<rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 10h18M7 14h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
      ['value' => $stats['total_predictions'] ?? 0, 'label' => 'TOTAL PRÉDICTIONS', 'delta' => 'générés par l\'IA', 'up' => true, 'tone' => 'lime',
       'icon' => '<path d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
      ['value' => ($stats['win_rate'] ?? 0).'%', 'label' => 'TAUX DE RÉUSSITE', 'delta' => 'sur prédictions résolues', 'up' => ($stats['win_rate'] ?? 0) >= 50, 'tone' => 'cool',
       'icon' => '<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none"/>'],
      ['value' => $stats['premium_count'] ?? 0, 'label' => 'PICKS PREMIUM (≥3★)', 'delta' => 'haute confiance', 'up' => true, 'tone' => 'cool',
       'icon' => '<rect x="3" y="6" width="18" height="13" rx="2.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="13.5" r="1.3" fill="currentColor" stroke="none"/>'],
    ];
  @endphp
  <div class="wd-kpis">
    @foreach($kpis as $kpi)
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" style="color:{{ $kpi['tone'] === 'cool' ? 'var(--cool)' : 'var(--accent)' }}">{!! $kpi['icon'] !!}</svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $kpi['value'] }}</div>
        <div class="wd-kpi-label">{{ $kpi['label'] }}</div>
        <div class="wd-kpi-delta" style="color:{{ $kpi['up'] ? 'var(--win)' : 'var(--loss)' }}">{{ $kpi['up'] ? '▲' : '▼' }} {{ $kpi['delta'] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Main columns --}}
  <div class="wd-cols">
    <div class="wd-maincol">

      {{-- À l'affiche --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Prédictions à l'affiche</div>
            <div class="wd-panel-sub">ANALYSÉES PAR L'IA COTA</div>
          </div>
          <a href="{{ route('predictions.index') }}" class="wd-morelink">Toutes les prédictions →</a>
        </div>
        <div class="wd-panel-body no-top">
          @php $topPreds = $predictions->flatten()->sortByDesc('confidence')->take(3) @endphp
          @if($topPreds->isEmpty())
            <div style="text-align:center;padding:32px;color:var(--dim);font-size:13.5px">Aucun pronostic disponible pour aujourd'hui.</div>
          @else
          <div class="wd-matches">
            @foreach($topPreds as $pred)
            @php $conf = round($pred->confidence ?? 0); $confColor = $conf >= 85 ? 'var(--accent)' : ($conf >= 70 ? 'var(--cool)' : 'var(--dim)'); @endphp
            <div class="wd-matchcard">
              <div class="wd-matchcard-back">
                <div style="position:absolute;top:12px;left:12px">
                  <span class="wd-pill" style="background:rgba(11,13,16,.62);color:var(--ink);border:1px solid var(--line2);font-size:9.5px">{{ strtoupper(Str::limit($pred->competition ?? '', 18)) }}</span>
                </div>
                <div style="position:absolute;bottom:12px;left:12px;right:12px">
                  <div style="font-family:var(--title);font-size:15px;color:var(--ink);letter-spacing:-.02em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ Str::limit(($pred->home_team ?? '?').' · '.($pred->away_team ?? '?'), 30) }}
                  </div>
                </div>
              </div>
              <div style="padding:12px 14px;display:flex;align-items:center;gap:12px">
                {{-- Confidence ring --}}
                <div style="position:relative;width:46px;height:46px;flex-shrink:0">
                  <svg viewBox="0 0 46 46" width="46" height="46" style="transform:rotate(-90deg)">
                    <circle cx="23" cy="23" r="18" fill="none" stroke="var(--bg3)" stroke-width="4"/>
                    <circle cx="23" cy="23" r="18" fill="none" stroke="{{ $confColor }}" stroke-width="4"
                      stroke-dasharray="{{ round(2*3.14159*18*$conf/100,1) }} {{ round(2*3.14159*18,1) }}"
                      stroke-linecap="round"/>
                  </svg>
                  <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;font-weight:700;color:{{ $confColor }}">{{ $conf }}</div>
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.14em">CONSEIL IA · {{ $pred->match_time ? substr($pred->match_time,0,5) : '—' }}</div>
                  <div style="font-size:13px;color:var(--ink);font-weight:600;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $pred->prediction_type ?? $pred->bet_type ?? 'Analyse' }}</div>
                </div>
                @if($pred->odds)
                <div style="font-family:var(--mono);font-size:14px;font-weight:700;color:{{ $conf >= 85 ? 'var(--accent)' : 'var(--ink)' }};white-space:nowrap">@{{ number_format($pred->odds,2) }}</div>
                @endif
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>

      {{-- Compétitions actives --}}
      @if($favoriteCompetitions->isNotEmpty())
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Compétitions actives</div>
            <div class="wd-panel-sub">AUJOURD'HUI · {{ $favoriteCompetitions->count() }} LIGUES</div>
          </div>
          <a href="{{ route('competitions') }}" class="wd-morelink">Voir tout →</a>
        </div>
        <div class="wd-panel-body no-top">
          <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach($favoriteCompetitions as $comp)
            <a href="{{ route('predictions.index', ['competition' => $comp['id']]) }}"
               style="display:flex;align-items:center;gap:8px;padding:9px 14px;background:var(--bg3);border:1px solid var(--line);border-radius:11px;text-decoration:none;transition:border-color .15s"
               onmouseover="this.style.borderColor='var(--line2)'" onmouseout="this.style.borderColor='var(--line)'">
              <span style="font-family:var(--title);font-size:13px;color:var(--ink)">{{ Str::limit($comp['name'], 22) }}</span>
              <span style="font-family:var(--mono);font-size:9px;background:var(--bg2);border-radius:4px;padding:2px 7px;color:var(--dim)">{{ $comp['count'] }}</span>
              @if($comp['live'])<span class="wd-livedot" style="color:var(--loss)"></span>@endif
            </a>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      {{-- Historique récent --}}
      @auth
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Mes derniers résultats</div>
            <div class="wd-panel-sub">HISTORIQUE RÉCENT</div>
          </div>
          <a href="{{ route('history') }}" class="wd-morelink">Historique complet →</a>
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
          @forelse($predictions->flatten()->take(5) as $pred)
          @php $st = $statMap[$pred->status] ?? $statMap['pending']; @endphp
          <div class="wd-tr">
            <div class="wd-cell wd-c-match">
              <div class="wd-thumb">
                <span style="font-family:var(--mono);font-size:8px;color:var(--dim)">{{ strtoupper(substr($pred->home_team??'?',0,3)) }}</span>
                <span style="font-family:var(--mono);font-size:7px;color:var(--dim2)">·</span>
                <span style="font-family:var(--mono);font-size:8px;color:var(--dim)">{{ strtoupper(substr($pred->away_team??'?',0,3)) }}</span>
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
          <div style="padding:16px 22px;color:var(--dim);font-size:13.5px">Aucun résultat disponible.</div>
          @endforelse
        </div>
      </div>
      @endauth

    </div>{{-- /maincol --}}

    {{-- Right rail --}}
    <div class="wd-rail">
      {{-- Stats --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Performances</div>
            <div class="wd-panel-sub">MODÈLE COTA · GLOBAL</div>
          </div>
          <span class="wd-pill" style="background:rgba(61,220,145,.12);color:var(--win)">▲ {{ $stats['win_rate'] ?? 0 }}%</span>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $total = max($stats['total_predictions'] ?? 1, 1);
            $sumItems = [
              ['label'=>'Prédictions générées','val'=>$stats['total_predictions']??0,'pct'=>100,'color'=>'var(--ink2)'],
              ['label'=>'Picks premium (≥3★)','val'=>$stats['premium_count']??0,'pct'=>round(($stats['premium_count']??0)/$total*100),'color'=>'var(--win)'],
              ['label'=>'Taux de réussite','val'=>($stats['win_rate']??0).'%','pct'=>$stats['win_rate']??0,'color'=>'var(--accent)'],
            ];
          @endphp
          @foreach($sumItems as $si)
          <div class="wd-sum-item">
            <div class="wd-sum-header">
              <span style="font-size:13px;color:var(--ink2)">{{ $si['label'] }}</span>
              <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $si['color'] }}">{{ $si['val'] }}</span>
            </div>
            <div class="wd-sum-track"><div class="wd-sum-fill" style="width:{{ $si['pct'] }}%;background:{{ $si['color'] }}"></div></div>
          </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:18px;padding-top:16px;border-top:1px solid var(--line)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.12em">RENDEMENT EST.</span>
            <span style="font-family:var(--title);font-size:24px;color:var(--accent);letter-spacing:-.03em">
              @php $wr = $stats['win_rate'] ?? 0; @endphp
              {{ $wr > 0 ? ($wr > 50 ? '+' : '').round(($wr - 50) * 0.4, 1).'%' : 'N/A' }}
            </span>
          </div>
        </div>
      </div>

      {{-- Accès rapide --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Accès rapide</div>
        </div>
        <div class="wd-panel-body no-top" style="display:flex;flex-direction:column;gap:12px">
          @php
            $shortcuts = [
              ['icon'=>'<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 8.5l6 3.5-6 3.5z" fill="currentColor" stroke="none"/>','label'=>'Matchs en live','href'=>route('live'),'tone'=>'var(--loss)','sub'=>'Suivre en direct'],
              ['icon'=>'<path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>','label'=>'Statistiques','href'=>route('statistics'),'tone'=>'var(--accent)','sub'=>'Vos performances'],
              ['icon'=>'<rect x="3" y="8" width="18" height="4" rx="1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 12v8h14v-8M12 8v12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 8S10.5 3 8 4.5 9.5 8 12 8Zm0 0s1.5-5 4-3.5S14.5 8 12 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>','label'=>'Parrainage','href'=>route('referral'),'tone'=>'var(--accent)','sub'=>'Inviter des amis'],
              ['icon'=>'<path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>','label'=>'Abonnement','href'=>route('subscription'),'tone'=>'var(--cool)','sub'=>'Passer Premium'],
            ];
          @endphp
          @foreach($shortcuts as $sc)
          <a href="{{ $sc['href'] }}" style="display:flex;align-items:center;gap:13px;padding:13px;background:var(--bg3);border-radius:11px;border:1px solid var(--line);text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--line2)'" onmouseout="this.style.borderColor='var(--line)'">
            <div style="width:42px;height:42px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:{{ $sc['tone'] }}">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="none">{!! $sc['icon'] !!}</svg>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-family:var(--title);font-size:14px;color:var(--ink);letter-spacing:-.01em">{{ $sc['label'] }}</div>
              <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.1em;margin-top:3px">{{ $sc['sub'] }}</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:var(--dim);flex-shrink:0"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          @endforeach
        </div>
      </div>

      @guest
      <div class="wd-panel" style="padding:24px;text-align:center">
        <div style="font-family:var(--title);font-size:16px;color:var(--ink);margin-bottom:8px">Accédez à tout COTA</div>
        <div style="font-size:13px;color:var(--dim);margin-bottom:18px;line-height:1.5">Créez un compte pour suivre vos prédictions, voir votre historique et accéder aux picks premium.</div>
        <a href="{{ route('register') }}" class="wd-cta wd-cta-block" style="display:flex;margin-bottom:10px">Créer un compte</a>
        <a href="{{ route('login') }}" style="font-family:var(--mono);font-size:11px;color:var(--dim);text-decoration:none;display:block;margin-top:8px">Déjà membre ? Se connecter →</a>
      </div>
      @endguest

    </div>{{-- /rail --}}
  </div>{{-- /cols --}}

</x-web-layout>
