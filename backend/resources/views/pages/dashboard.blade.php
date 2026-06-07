@php
  $todayLabel = strtoupper(now()->formatLocalized('%a. %d %b %Y') ?: now()->format('D. d M Y'));
  $winRate = $stats['win_rate'] ?? 0;
  $totalPreds = $stats['total_predictions'] ?? 0;
  $premiumCount = $stats['premium_count'] ?? 0;
  $todayCount = $stats['today_count'] ?? 0;
  $user = auth()->user();
@endphp

<x-web-layout pageTitle="Dashboard — COTA">
  <!-- Topbar -->
  <div class="wd-topbar">
    <div>
      <div class="wd-date">{{ strtoupper(now()->locale('fr')->isoFormat('ddd. D MMM YYYY')) }}</div>
      <h1 class="wd-h1">
        @auth Bon retour, {{ explode(' ', auth()->user()->name)[0] }}. @else Bienvenue sur COTA. @endauth
      </h1>
    </div>
    <div class="wd-topactions">
      @auth
      <a href="#" class="wd-iconbtn" title="Notifications">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 19a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="wd-dot"></span>
      </a>
      @endauth
      <a href="{{ route('predictions.index') }}" class="wd-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Pronostics du jour
      </a>
    </div>
  </div>

  <!-- Filter bar -->
  <form method="GET" action="{{ route('home') }}" class="wd-filterbar">
    <div class="wd-search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="m20 20-3.2-3.2" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <input type="text" placeholder="Rechercher un match, une compétition…" name="q" value="{{ request('q') }}">
    </div>
    <button type="button" class="wd-filter" onclick="this.nextElementSibling && this.nextElementSibling.focus()">
      <span class="wd-filter-label">COMPÉTITION</span>
      <div class="wd-filter-val">
        <span>Toutes</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </button>
    <button type="button" class="wd-filter">
      <span class="wd-filter-label">DATE</span>
      <div class="wd-filter-val">
        <span>Aujourd'hui</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </button>
    <button type="button" class="wd-filter">
      <span class="wd-filter-label">CONFIANCE MIN.</span>
      <div class="wd-filter-val">
        <span>Tous niveaux</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </button>
    <a href="{{ route('predictions.index') }}" class="wd-filterbtn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="var(--bg)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Filtrer
    </a>
  </form>

  <!-- KPIs -->
  <div class="wd-kpis">
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 10h18M7 14h7" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $todayCount }}</div>
        <div class="wd-kpi-label">PRONOSTICS AUJOURD'HUI</div>
        <div class="wd-kpi-delta" style="color:var(--win)">▲ {{ $premiumCount }} premium</div>
      </div>
    </div>
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $totalPreds }}</div>
        <div class="wd-kpi-label">TOTAL PRÉDICTIONS</div>
        <div class="wd-kpi-delta" style="color:var(--win)">▲ générés par l'IA</div>
      </div>
    </div>
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="var(--cool)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3.6" stroke="var(--cool)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="0.6" fill="var(--cool)" stroke="none"/></svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $winRate }}%</div>
        <div class="wd-kpi-label">TAUX DE RÉUSSITE</div>
        <div class="wd-kpi-delta" style="color:var(--cool)">sur prédictions résolues</div>
      </div>
    </div>
    <div class="wd-kpi">
      <div class="wd-kpi-shape">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="13" rx="2.5" stroke="var(--cool)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 9h18" stroke="var(--cool)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="13.5" r="1.3" fill="var(--cool)" stroke="none"/></svg>
      </div>
      <div class="wd-kpi-body">
        <div class="wd-kpi-value">{{ $premiumCount }}</div>
        <div class="wd-kpi-label">PICKS PREMIUM (≥3★)</div>
        <div class="wd-kpi-delta" style="color:var(--cool)">haute confiance</div>
      </div>
    </div>
  </div>

  <!-- Main columns -->
  <div class="wd-cols">
    <div class="wd-maincol">
      <!-- À l'affiche -->
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Prédictions du jour</div>
            <div class="wd-panel-sub">ANALYSÉES PAR L'IA COTA</div>
          </div>
          <a href="{{ route('predictions.index') }}" class="wd-morelink">Toutes les prédictions →</a>
        </div>
        <div class="wd-panel-body no-top">
          @if($predictions->isEmpty())
            <div style="text-align:center;padding:32px;color:var(--dim);font-size:13.5px">Aucun pronostic disponible pour aujourd'hui.</div>
          @else
            <div class="wd-matches">
              @foreach($predictions->take(3) as $comp => $group)
                @php $pred = $group->first() @endphp
                <div class="wd-matchcard">
                  <div class="wd-matchcard-back" style="background:linear-gradient(135deg,var(--bg3) 0%,var(--bg2) 100%)">
                    <div style="position:absolute;top:12px;left:12px">
                      <span class="wd-pill" style="background:rgba(11,13,16,.62);color:var(--ink);border:1px solid var(--line2);font-size:9.5px">
                        {{ strtoupper(Str::limit($comp, 20)) }}
                      </span>
                    </div>
                    <div style="position:absolute;bottom:12px;left:12px;right:12px;display:flex;align-items:center;gap:10px">
                      <div style="flex:1;font-family:var(--title);font-size:15px;color:var(--ink);letter-spacing:-.02em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ Str::limit($pred->home_team . ' — ' . $pred->away_team, 32) }}
                      </div>
                    </div>
                  </div>
                  <div style="padding:12px 14px;display:flex;align-items:center;gap:12px">
                    <div style="width:46px;height:46px;border-radius:50%;background:conic-gradient(var(--accent) {{ $pred->confidence }}%, var(--bg3) 0);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                      <div style="width:34px;height:34px;border-radius:50%;background:var(--bg2);display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;font-weight:700;color:var(--accent)">{{ round($pred->confidence) }}</div>
                    </div>
                    <div style="flex:1;min-width:0">
                      <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.14em">CONSEIL IA · {{ $pred->match_time ?? '—' }}</div>
                      <div style="font-size:13px;color:var(--ink);font-weight:600;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $pred->prediction_type ?? $pred->bet_type ?? 'Analyse disponible' }}</div>
                    </div>
                    @if($pred->odds)
                    <div style="font-family:var(--mono);font-size:14px;font-weight:700;color:{{ $pred->confidence >= 85 ? 'var(--accent)' : 'var(--ink)' }};white-space:nowrap">@{{ number_format($pred->odds, 2) }}</div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      <!-- Compétitions du jour -->
      @if($favoriteCompetitions->isNotEmpty())
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Compétitions actives</div>
            <div class="wd-panel-sub">AUJOURD'HUI</div>
          </div>
          <a href="{{ route('competitions') }}" class="wd-morelink">Toutes →</a>
        </div>
        <div class="wd-panel-body no-top">
          <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach($favoriteCompetitions as $comp)
            <a href="{{ route('predictions.index', ['competition' => $comp['id']]) }}" style="display:flex;align-items:center;gap:8px;padding:9px 14px;background:var(--bg3);border:1px solid var(--line);border-radius:11px;text-decoration:none;transition:border-color .15s">
              <span style="font-family:var(--title);font-size:13px;color:var(--ink)">{{ Str::limit($comp['name'], 22) }}</span>
              <span style="font-family:var(--mono);font-size:9px;background:var(--bg2);border-radius:4px;padding:2px 7px;color:var(--dim)">{{ $comp['count'] }}</span>
              @if($comp['live'])
              <span class="wd-livedot" style="color:var(--loss)"></span>
              @endif
            </a>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      <!-- Historique récent -->
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
            <div class="wd-cell wd-c-gain" style="text-align:right">Résultat</div>
          </div>
          @forelse($predictions->flatten()->take(5) as $pred)
          @php
            $st = ['won' => ['label'=>'Gagné','color'=>'var(--win)','bg'=>'rgba(61,220,145,.12)'], 'lost' => ['label'=>'Perdu','color'=>'var(--loss)','bg'=>'rgba(255,91,58,.12)'], 'pending' => ['label'=>'En attente','color'=>'var(--accent)','bg'=>'rgba(232,255,54,.1)'], 'live' => ['label'=>'Live','color'=>'var(--loss)','bg'=>'rgba(255,91,58,.12)']];
            $s = $st[$pred->status] ?? $st['pending'];
          @endphp
          <div class="wd-tr">
            <div class="wd-cell wd-c-match">
              <div class="wd-thumb">
                <span style="font-family:var(--mono);font-size:8px;color:var(--dim)">{{ strtoupper(substr($pred->home_team ?? '?', 0, 3)) }}</span>
                <span style="font-family:var(--mono);font-size:8px;color:var(--dim2)">·</span>
                <span style="font-family:var(--mono);font-size:8px;color:var(--dim)">{{ strtoupper(substr($pred->away_team ?? '?', 0, 3)) }}</span>
              </div>
              <div style="min-width:0">
                <div style="font-family:var(--title);font-size:14px;color:var(--ink);letter-spacing:-.01em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ Str::limit(($pred->home_team ?? '?') . ' – ' . ($pred->away_team ?? '?'), 28) }}</div>
                <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-top:3px">{{ $pred->match_date ? \Carbon\Carbon::parse($pred->match_date)->format('d M Y') : '—' }}</div>
              </div>
            </div>
            <div class="wd-cell wd-c-pick" style="font-size:13px;color:var(--ink2)">{{ Str::limit($pred->prediction_type ?? $pred->bet_type ?? '—', 24) }}</div>
            <div class="wd-cell wd-c-odds" style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--ink)">{{ $pred->odds ? '@'.$pred->odds : '—' }}</div>
            <div class="wd-cell wd-c-stat">
              <span class="wd-badge" style="color:{{ $s['color'] }};background:{{ $s['bg'] }}">
                @if($pred->status === 'live')<span class="wd-livedot"></span>@endif
                {{ $s['label'] }}
              </span>
            </div>
            <div class="wd-cell wd-c-gain" style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $pred->status === 'won' ? 'var(--win)' : ($pred->status === 'lost' ? 'var(--loss)' : 'var(--dim)') }};text-align:right">
              {{ $pred->status === 'won' ? '+' : ($pred->status === 'lost' ? '−' : '') }}{{ $pred->status === 'pending' || $pred->status === 'live' ? '—' : '—' }}
            </div>
          </div>
          @empty
          <div class="wd-tr"><div style="color:var(--dim);font-size:13.5px;grid-column:1/-1;padding:8px 0">Aucun résultat disponible.</div></div>
          @endforelse
        </div>
      </div>
      @endauth
    </div>

    <!-- Right rail -->
    <div class="wd-rail">
      <!-- Stats summary -->
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Performances</div>
            <div class="wd-panel-sub">COTA · GLOBAL</div>
          </div>
          <span class="wd-pill" style="background:rgba(61,220,145,.12);color:var(--win)">▲ {{ $winRate }}%</span>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $items = [
              ['label' => 'Pronostics générés', 'value' => $totalPreds, 'pct' => min(100, ($totalPreds / max($totalPreds, 1)) * 100), 'color' => 'var(--ink2)'],
              ['label' => 'Picks premium', 'value' => $premiumCount, 'pct' => $totalPreds > 0 ? round($premiumCount / $totalPreds * 100) : 0, 'color' => 'var(--win)'],
              ['label' => 'Taux réussite', 'value' => $winRate . '%', 'pct' => $winRate, 'color' => 'var(--accent)'],
            ];
          @endphp
          @foreach($items as $item)
          <div class="wd-sum-item">
            <div class="wd-sum-header">
              <span style="font-size:13px;color:var(--ink2)">{{ $item['label'] }}</span>
              <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $item['color'] }}">{{ $item['value'] }}</span>
            </div>
            <div class="wd-sum-track"><div class="wd-sum-fill" style="width:{{ $item['pct'] }}%;background:{{ $item['color'] }}"></div></div>
          </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:18px;padding-top:16px;border-top:1px solid var(--line)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.12em">RENDEMENT</span>
            <span style="font-family:var(--title);font-size:24px;color:var(--accent);letter-spacing:-.03em">{{ $winRate > 0 ? '+'.round(($winRate - 50) * 0.5, 1).'%' : 'N/A' }}</span>
          </div>
        </div>
      </div>

      <!-- Accès rapide -->
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Accès rapide</div>
        </div>
        <div class="wd-panel-body no-top" style="display:flex;flex-direction:column;gap:12px">
          @php
            $shortcuts = [
              ['icon' => '<svg width="19" height="19" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 8.5l6 3.5-6 3.5z" fill="currentColor" stroke="none"/></svg>', 'label' => 'Matchs en live', 'href' => route('live'), 'tone' => 'var(--loss)', 'sub' => 'Suivre en direct'],
              ['icon' => '<svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'label' => 'Statistiques', 'href' => route('statistics'), 'tone' => 'var(--accent)', 'sub' => 'Vos performances'],
              ['icon' => '<svg width="19" height="19" viewBox="0 0 24 24" fill="none"><rect x="3" y="8" width="18" height="4" rx="1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 12v8h14v-8M12 8v12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 8S10.5 3 8 4.5 9.5 8 12 8Zm0 0s1.5-5 4-3.5S14.5 8 12 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'label' => 'Parrainage', 'href' => route('referral'), 'tone' => 'var(--accent)', 'sub' => 'Inviter des amis'],
              ['icon' => '<svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'label' => 'Abonnement', 'href' => route('subscription'), 'tone' => 'var(--cool)', 'sub' => 'Passer Premium'],
            ];
          @endphp
          @foreach($shortcuts as $sc)
          <a href="{{ $sc['href'] }}" style="display:flex;align-items:center;gap:13px;padding:13px;background:var(--bg3);border-radius:11px;border:1px solid var(--line);text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--line2)'" onmouseout="this.style.borderColor='var(--line)'">
            <div style="width:42px;height:42px;border-radius:11px;background:rgba(255,255,255,.04);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:{{ $sc['tone'] }}">
              {!! $sc['icon'] !!}
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-family:var(--title);font-size:14px;color:var(--ink);letter-spacing:-.01em">{{ $sc['label'] }}</div>
              <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.1em;margin-top:3px">{{ $sc['sub'] }}</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:var(--dim)"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          @endforeach
        </div>
      </div>

      @guest
      <!-- CTA connexion -->
      <div class="wd-panel" style="padding:24px;text-align:center">
        <div style="font-family:var(--title);font-size:16px;color:var(--ink);margin-bottom:8px">Accédez à tout COTA</div>
        <div style="font-size:13px;color:var(--dim);margin-bottom:18px;line-height:1.5">Créez un compte pour suivre vos prédictions, voir votre historique et accéder aux picks premium.</div>
        <a href="{{ route('register') }}" class="wd-cta wd-cta-block" style="display:flex;margin-bottom:10px">Créer un compte</a>
        <a href="{{ route('login') }}" style="font-family:var(--mono);font-size:11px;color:var(--dim);text-decoration:none;display:block;margin-top:8px">Déjà membre ? Se connecter →</a>
      </div>
      @endguest
    </div>
  </div>
</x-web-layout>
