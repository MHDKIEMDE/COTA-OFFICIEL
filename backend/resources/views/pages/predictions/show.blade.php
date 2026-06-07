@php
    $isLive    = ($prediction->status ?? '') === 'live';
    $isDone    = in_array($prediction->status ?? '', ['won','lost','finished','cancelled']);
    $stars     = $prediction->confidence_stars ?? 0;
    $score     = $prediction->total_score ?? $prediction->confidence ?? 0;
    $odds      = $prediction->odds ?? $prediction->estimated_odds ?? 0;
    $outcome   = $prediction->predicted_outcome ?? $prediction->prediction ?? '—';
    $betType   = $prediction->bet_type ?? $prediction->prediction_type ?? 'Résultat';
    $isPremium = $prediction->is_premium ?? false;
    $canSee    = !$isPremium || (auth()->check() && (auth()->user()->is_premium ?? false));
    $matchDate = isset($prediction->match_date) ? \Carbon\Carbon::parse($prediction->match_date) : null;
@endphp

<x-web-layout pageTitle="{{ ($prediction->home_team ?? '?').' vs '.($prediction->away_team ?? '?').' — COTA' }}">
<style>
    .sh-hero { background:var(--bg2);border:1px solid var(--line);border-radius:16px;padding:22px;position:relative;overflow:hidden;margin-bottom:22px; }
    .sh-hero::before { content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(232,255,54,.04) 0%,transparent 60%),radial-gradient(ellipse at 80% 50%,rgba(61,220,145,.04) 0%,transparent 60%);pointer-events:none; }
    .sh-hero__comp { display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:18px; }
    .sh-hero__comp-name { font-family:var(--mono);font-size:10px;font-weight:700;color:var(--dim);letter-spacing:.14em;text-transform:uppercase; }
    .sh-hero__badge { font-family:var(--mono);font-size:9px;font-weight:700;padding:2px 8px;border-radius:4px; }
    .sh-hero__vs { display:flex;align-items:center;gap:10px; }
    .sh-hero__team { flex:1;display:flex;flex-direction:column;align-items:center;gap:8px; }
    .sh-hero__logo { width:56px;height:56px;border-radius:10px;object-fit:contain;background:var(--bg3); }
    .sh-hero__logo--placeholder { display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;font-weight:800;color:var(--dim);border:1px solid var(--line); }
    .sh-hero__team-name { font-family:var(--ui);font-size:14px;font-weight:700;color:var(--ink);text-align:center;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .sh-hero__team-role { font-family:var(--mono);font-size:8px;color:var(--dim);letter-spacing:.5px; }
    .sh-hero__center { display:flex;flex-direction:column;align-items:center;gap:4px;flex-shrink:0;min-width:80px; }
    .sh-hero__score { font-family:var(--title);font-size:34px;color:var(--ink);letter-spacing:-2px;line-height:1; }
    .sh-hero__score--live { color:var(--loss); }
    .sh-hero__time { font-family:var(--title);font-size:24px;color:var(--accent);line-height:1; }
    .sh-hero__minute { font-family:var(--mono);font-size:11px;font-weight:700;color:var(--loss); }
    .sh-hero__date { font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.3px; }
    .sh-hero__vs-label { font-family:var(--mono);font-size:11px;font-weight:700;color:var(--dim); }
    .sh-tabs { display:flex;border-bottom:1px solid var(--line);margin-bottom:22px; }
    .sh-tab { flex:1;text-align:center;padding:11px 0;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--dim);border-bottom:2px solid transparent;cursor:pointer;text-decoration:none;transition:color .15s,border-color .15s; }
    .sh-tab.active { color:var(--accent);border-color:var(--accent); }
    .sh-pred { background:var(--bg2);border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:18px; }
    .sh-pred--premium { border-color:rgba(232,255,54,.3); }
    .sh-pred__head { display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid var(--line2);background:rgba(232,255,54,.04); }
    .sh-pred__head-title { display:flex;align-items:center;gap:7px;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--accent); }
    .sh-pred__prem-badge { font-family:var(--mono);font-size:8px;font-weight:700;background:rgba(232,255,54,.15);color:var(--accent);border:1px solid rgba(232,255,54,.3);padding:2px 7px;border-radius:4px; }
    .sh-pred__body { padding:16px; }
    .sh-pred__bet { display:flex;align-items:center;justify-content:space-between;background:var(--bg3);border-radius:10px;padding:13px 16px;margin-bottom:14px; }
    .sh-pred__bet-type { font-family:var(--mono);font-size:9px;font-weight:700;color:var(--dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px; }
    .sh-pred__bet-val { font-family:var(--ui);font-size:17px;font-weight:700;color:var(--ink); }
    .sh-pred__odds { text-align:right; }
    .sh-pred__odds-lbl { font-family:var(--mono);font-size:9px;color:var(--dim);margin-bottom:2px; }
    .sh-pred__odds-val { font-family:var(--title);font-size:26px;color:var(--accent);line-height:1; }
    .sh-pred__stars { display:flex;align-items:center;gap:3px;margin-bottom:12px; }
    .sh-pred__star { font-size:13px;color:var(--accent); }
    .sh-pred__star--off { color:var(--line2); }
    .sh-pred__bar-wrap { display:flex;align-items:center;gap:10px; }
    .sh-pred__bar { flex:1;height:6px;background:var(--line);border-radius:3px;overflow:hidden; }
    .sh-pred__bar-fill { height:100%;border-radius:3px;background:var(--accent);width:0;transition:width 1s ease; }
    .sh-pred__score-lbl { font-family:var(--mono);font-size:12px;font-weight:700;color:var(--dim);white-space:nowrap; }
    .sh-pred__result { display:flex;align-items:center;gap:8px;padding:11px 14px;border-radius:9px;margin-top:12px;font-family:var(--ui);font-size:14px;font-weight:700; }
    .sh-pred__result--won { background:rgba(61,220,145,.12);color:var(--win); }
    .sh-pred__result--lost { background:rgba(255,91,58,.12);color:var(--loss); }
    .sh-section { background:var(--bg2);border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:18px; }
    .sh-section__head { display:flex;align-items:center;gap:8px;padding:13px 16px;border-bottom:1px solid var(--line2);font-family:var(--ui);font-size:13px;font-weight:700;color:var(--dim); }
    .sh-section__head svg { color:var(--accent); }
    .sh-section__badge { margin-left:auto;font-family:var(--mono);font-size:10px;font-weight:700;color:var(--accent); }
    .sh-section__body { padding:16px; }
    .crit-row { display:flex;align-items:center;gap:12px;margin-bottom:12px; }
    .crit-row:last-child { margin-bottom:0; }
    .crit-row__label { display:flex;align-items:center;gap:7px;width:115px;flex-shrink:0;font-family:var(--ui);font-size:12px;font-weight:600;color:var(--dim); }
    .crit-row__bar { flex:1;height:5px;background:var(--line);border-radius:2px;overflow:hidden; }
    .crit-row__bar-fill { height:100%;border-radius:2px;width:0;transition:width 1s ease; }
    .crit-row__score { font-family:var(--mono);font-size:10px;font-weight:700;color:var(--ink);white-space:nowrap;min-width:36px;text-align:right; }
    .crit-row__max { color:var(--dim); }
    .h2h-row { display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid var(--line2); }
    .h2h-row:last-child { border-bottom:none; }
    .h2h-row__date { font-family:var(--mono);font-size:9px;color:var(--dim);width:36px;flex-shrink:0; }
    .h2h-row__home { flex:1;text-align:right; }
    .h2h-row__away { flex:1; }
    .h2h-row__team { font-family:var(--ui);font-size:12px;font-weight:600;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .h2h-row__score { font-family:var(--title);font-size:15px;color:var(--ink);white-space:nowrap;padding:0 8px; }
    .h2h-row__res { font-family:var(--mono);font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;flex-shrink:0; }
    .h2h-summary { display:flex;justify-content:center;gap:28px;padding:16px 0 2px;border-top:1px solid var(--line2);margin-top:10px; }
    .h2h-summary__item { text-align:center; }
    .h2h-summary__count { font-family:var(--title);font-size:24px;line-height:1; }
    .h2h-summary__label { font-family:var(--ui);font-size:10px;color:var(--dim);margin-top:2px; }
</style>

  <a href="{{ url()->previous() }}" class="wd-back-btn">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="transform:rotate(90deg)"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Retour
  </a>

  <div class="wd-detail">
    <div>
      {{-- Hero --}}
      <div class="sh-hero">
        <div class="sh-hero__comp">
          @if($prediction->competition_logo ?? null)<img src="{{ $prediction->competition_logo }}" alt="" style="width:16px;height:16px;border-radius:3px;">@endif
          <span class="sh-hero__comp-name">{{ $prediction->competition ?? 'Football' }}</span>
          @if($isLive)<span class="sh-hero__badge" style="background:var(--loss);color:#fff;">● LIVE</span>
          @elseif($isDone)<span class="sh-hero__badge" style="background:var(--bg3);color:var(--dim);">FT</span>@endif
        </div>
        <div class="sh-hero__vs">
          <div class="sh-hero__team">
            @if($prediction->home_team_logo ?? null)<img src="{{ $prediction->home_team_logo }}" alt="" class="sh-hero__logo">
            @else<div class="sh-hero__logo sh-hero__logo--placeholder">{{ strtoupper(substr($prediction->home_team ?? '?', 0, 3)) }}</div>@endif
            <span class="sh-hero__team-name">{{ $prediction->home_team ?? '—' }}</span>
            <span class="sh-hero__team-role">DOMICILE</span>
          </div>
          <div class="sh-hero__center">
            @if($isLive)
              <div class="sh-hero__score sh-hero__score--live">{{ $prediction->home_score ?? 0 }}&nbsp;-&nbsp;{{ $prediction->away_score ?? 0 }}</div>
              <span class="sh-hero__minute">{{ $prediction->live_minute ?? '?' }}'</span>
            @elseif($isDone && isset($prediction->home_score))
              <div class="sh-hero__score">{{ $prediction->home_score }}&nbsp;-&nbsp;{{ $prediction->away_score }}</div>
              <span class="sh-hero__date">SCORE FINAL</span>
            @else
              <div class="sh-hero__time">{{ $matchDate ? $matchDate->format('H:i') : '--:--' }}</div>
              <span class="sh-hero__vs-label">VS</span>
              @if($matchDate)<span class="sh-hero__date">{{ strtoupper($matchDate->locale('fr')->isoFormat('ddd D MMM')) }}</span>@endif
            @endif
          </div>
          <div class="sh-hero__team">
            @if($prediction->away_team_logo ?? null)<img src="{{ $prediction->away_team_logo }}" alt="" class="sh-hero__logo">
            @else<div class="sh-hero__logo sh-hero__logo--placeholder">{{ strtoupper(substr($prediction->away_team ?? '?', 0, 3)) }}</div>@endif
            <span class="sh-hero__team-name">{{ $prediction->away_team ?? '—' }}</span>
            <span class="sh-hero__team-role">EXTÉRIEUR</span>
          </div>
        </div>
      </div>

      {{-- Tabs --}}
      <div class="sh-tabs">
        <a href="#" class="sh-tab active" onclick="shTab('resume',this);return false;">⚡ Résumé</a>
        <a href="#" class="sh-tab" onclick="shTab('h2h',this);return false;">⇄ H2H</a>
        <a href="#" class="sh-tab" onclick="shTab('standings',this);return false;">≡ Classement</a>
      </div>

      {{-- ONGLET RÉSUMÉ --}}
      <div id="pane-resume">
        <div class="sh-pred {{ $isPremium ? 'sh-pred--premium' : '' }}">
          <div class="sh-pred__head">
            <div class="sh-pred__head-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Pronostic COTA
            </div>
            @if($isPremium)<span class="sh-pred__prem-badge">⭐ PREMIUM</span>@endif
          </div>
          <div class="sh-pred__body">
            @if($canSee)
              <div class="sh-pred__bet">
                <div>
                  <div class="sh-pred__bet-type">{{ strtoupper($betType) }}</div>
                  <div class="sh-pred__bet-val">{{ $outcome }}</div>
                </div>
                @if($odds)
                <div class="sh-pred__odds">
                  <div class="sh-pred__odds-lbl">COTE</div>
                  <div class="sh-pred__odds-val">@{{ number_format($odds, 2) }}</div>
                </div>
                @endif
              </div>
              <div class="sh-pred__stars">
                @for($s = 1; $s <= 4; $s++)<span class="sh-pred__star {{ $s <= $stars ? '' : 'sh-pred__star--off' }}">★</span>@endfor
                <span style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-left:6px;">{{ $stars }}/4 étoiles</span>
              </div>
              <div class="sh-pred__bar-wrap">
                <div class="sh-pred__bar">
                  <div class="sh-pred__bar-fill" id="scoreFill" style="background:{{ $score >= 70 ? 'var(--win)' : ($score >= 55 ? 'var(--accent)' : 'var(--loss)') }};"></div>
                </div>
                <span class="sh-pred__score-lbl">{{ $score }}/100</span>
              </div>
              @if(in_array($prediction->status ?? '', ['won','lost']))
                <div class="sh-pred__result sh-pred__result--{{ $prediction->status }}">
                  {{ $prediction->status === 'won' ? '✓ Pronostic GAGNÉ !' : '✗ Pronostic PERDU' }}
                </div>
              @endif
            @else
              <div style="text-align:center;padding:22px 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" style="color:var(--accent);margin:0 auto 10px;display:block;"><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <div style="font-family:var(--ui);font-size:14px;font-weight:700;color:var(--ink);margin-bottom:6px;">Contenu Premium</div>
                <div style="font-family:var(--ui);font-size:12px;color:var(--dim);margin-bottom:16px;">Abonne-toi pour voir ce pronostic 3–4 étoiles</div>
                <a href="{{ route('subscription') }}" class="wd-cta">Passer Premium →</a>
              </div>
            @endif
          </div>
        </div>

        {{-- Analyse texte --}}
        @php
          $analysis = is_string($prediction->analysis_details ?? null)
            ? json_decode($prediction->analysis_details, true)
            : ($prediction->analysis_details ?? null);
        @endphp
        @if(!empty($analysis['reasoning']))
          <div class="sh-section">
            <div class="sh-section__head">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Analyse COTA
            </div>
            <div class="sh-section__body">
              <p style="font-family:var(--ui);font-size:13px;color:var(--dim);line-height:1.6;margin:0;">{{ $analysis['reasoning'] }}</p>
            </div>
          </div>
        @endif

        {{-- Critères --}}
        @php
          $criteria = [
            ['label'=>'Forme récente', 'value'=>$prediction->score_form      ?? 0, 'max'=>25, 'color'=>'#3B82F6'],
            ['label'=>'Face à face',   'value'=>$prediction->score_h2h       ?? 0, 'max'=>20, 'color'=>'#8B5CF6'],
            ['label'=>'Dom / Ext',     'value'=>$prediction->score_home_away ?? 0, 'max'=>15, 'color'=>'#3ddc91'],
            ['label'=>'Classement',    'value'=>$prediction->score_league    ?? 0, 'max'=>12, 'color'=>'#F59E0B'],
            ['label'=>'Buts',          'value'=>$prediction->score_goals     ?? 0, 'max'=>10, 'color'=>'#EF4444'],
            ['label'=>'Tirs cadrés',   'value'=>$prediction->score_shots     ?? 0, 'max'=>3,  'color'=>'#06B6D4'],
            ['label'=>'Physique',      'value'=>$prediction->score_physical  ?? 0, 'max'=>2,  'color'=>'#84CC16'],
          ];
        @endphp
        <div class="sh-section">
          <div class="sh-section__head">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Critères d'analyse
            <span class="sh-section__badge">{{ $score }}/100</span>
          </div>
          <div class="sh-section__body">
            @foreach($criteria as $c)
              @php $pct = $c['max'] > 0 ? round(($c['value'] / $c['max']) * 100) : 0; @endphp
              <div class="crit-row">
                <div class="crit-row__label" style="color:{{ $c['color'] }}">{{ $c['label'] }}</div>
                <div class="crit-row__bar"><div class="crit-row__bar-fill crit-anim" style="background:{{ $c['color'] }};" data-w="{{ $pct }}"></div></div>
                <span class="crit-row__score">{{ number_format($c['value'], 1) }}<span class="crit-row__max">/{{ $c['max'] }}</span></span>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ONGLET H2H --}}
      <div id="pane-h2h" style="display:none;">
        <div class="sh-section">
          <div class="sh-section__head">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3m13-5v3a2 2 0 0 1-2 2h-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Confrontations directes
          </div>
          <div class="sh-section__body">
            @php $h2hMatches = $headToHead ?? []; $resColors = ['W'=>'#3ddc91','D'=>'#F59E0B','L'=>'#ff5b3a']; @endphp
            @if(!empty($h2hMatches))
              @foreach($h2hMatches as $m)
                @php $res = ($m->home_score ?? 0) > ($m->away_score ?? 0) ? 'W' : (($m->home_score ?? 0) < ($m->away_score ?? 0) ? 'L' : 'D'); @endphp
                <div class="h2h-row">
                  <span class="h2h-row__date">{{ isset($m->match_date) ? \Carbon\Carbon::parse($m->match_date)->format('d/m') : '—' }}</span>
                  <div class="h2h-row__home"><span class="h2h-row__team">{{ $m->home_team ?? '—' }}</span></div>
                  <span class="h2h-row__score">{{ $m->home_score ?? '-' }} – {{ $m->away_score ?? '-' }}</span>
                  <div class="h2h-row__away"><span class="h2h-row__team">{{ $m->away_team ?? '—' }}</span></div>
                  <span class="h2h-row__res" style="background:{{ $resColors[$res] }}20;color:{{ $resColors[$res] }};">{{ $res }}</span>
                </div>
              @endforeach
              @php
                $h2hCol = collect($h2hMatches);
                $wins   = $h2hCol->filter(fn($m) => ($m->home_score ?? 0) > ($m->away_score ?? 0))->count();
                $draws  = $h2hCol->filter(fn($m) => ($m->home_score ?? 0) === ($m->away_score ?? 0))->count();
                $losses = $h2hCol->filter(fn($m) => ($m->home_score ?? 0) < ($m->away_score ?? 0))->count();
              @endphp
              <div class="h2h-summary">
                <div class="h2h-summary__item"><div class="h2h-summary__count" style="color:var(--win);">{{ $wins }}</div><div class="h2h-summary__label">Victoires</div></div>
                <div class="h2h-summary__item"><div class="h2h-summary__count" style="color:#F59E0B;">{{ $draws }}</div><div class="h2h-summary__label">Nuls</div></div>
                <div class="h2h-summary__item"><div class="h2h-summary__count" style="color:var(--loss);">{{ $losses }}</div><div class="h2h-summary__label">Défaites</div></div>
              </div>
            @else
              <div style="text-align:center;padding:20px 0;color:var(--dim);font-size:13.5px">Historique non disponible pour ce match.</div>
            @endif
          </div>
        </div>
      </div>

      {{-- ONGLET CLASSEMENT --}}
      <div id="pane-standings" style="display:none;">
        <div class="sh-section">
          <div class="sh-section__head">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 4h12v4a6 6 0 0 1-12 0V4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Classement — {{ $prediction->competition ?? '—' }}
          </div>
          <div class="sh-section__body">
            <div style="text-align:center;padding:20px 0;color:var(--dim);font-size:13.5px">Classement non disponible pour cette compétition.</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Rail droite --}}
    <div class="wd-detail-rail">
      {{-- Recommandation synthèse --}}
      <div class="wd-panel" style="padding:22px;text-align:center">
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.14em;margin-bottom:14px">SCORE DE CONFIANCE</div>
        @php $confColor = $score >= 70 ? 'var(--win)' : ($score >= 55 ? 'var(--accent)' : 'var(--loss)'); $circ2 = round(2*3.14159*42,1); $arc2 = round(2*3.14159*42*$score/100,1); @endphp
        <div style="position:relative;width:108px;height:108px;margin:0 auto 14px">
          <svg viewBox="0 0 108 108" width="108" height="108" style="transform:rotate(-90deg)">
            <circle cx="54" cy="54" r="42" fill="none" stroke="var(--bg3)" stroke-width="8"/>
            <circle cx="54" cy="54" r="42" fill="none" stroke="{{ $confColor }}" stroke-width="8" stroke-dasharray="{{ $arc2 }} {{ $circ2 }}" stroke-linecap="round"/>
          </svg>
          <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
            <div style="font-family:var(--title);font-size:24px;color:{{ $confColor }};letter-spacing:-.03em">{{ $score }}</div>
            <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.1em">/ 100</div>
          </div>
        </div>
        <div style="font-family:var(--title);font-size:18px;color:var(--ink);margin-bottom:6px">{{ $outcome }}</div>
        @if($odds)
        <div style="font-family:var(--mono);font-size:18px;font-weight:700;padding:7px 16px;border-radius:10px;display:inline-block;color:var(--accent);background:var(--accent-dim);border:1px solid rgba(232,255,54,.3);">@{{ number_format($odds,2) }}</div>
        @endif
      </div>

      {{-- Share --}}
      <div style="display:flex;gap:10px">
        <button onclick="if(navigator.share)navigator.share({title:document.title,url:window.location.href});else navigator.clipboard.writeText(window.location.href);"
                class="wd-ghost-btn" style="flex:1;justify-content:center">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="1.7"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="1.7"/><path d="m8.59 13.51 6.83 3.98M15.41 6.51l-6.82 3.98" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
          Partager
        </button>
        <a href="{{ route('subscription') }}" class="wd-cta" style="flex:1;justify-content:center">Premium</a>
      </div>
    </div>
  </div>

<script>
function shTab(name, el) {
  ['resume','h2h','standings'].forEach(id => {
    document.getElementById('pane-' + id).style.display = id === name ? 'block' : 'none';
  });
  document.querySelectorAll('.sh-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
document.addEventListener('DOMContentLoaded', function () {
  const fill = document.getElementById('scoreFill');
  if (fill) setTimeout(() => fill.style.width = '{{ $score }}%', 200);
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.style.width = e.target.dataset.w + '%'; obs.unobserve(e.target); } });
  }, { threshold: 0.2 });
  document.querySelectorAll('.crit-anim').forEach(b => obs.observe(b));
});
</script>

</x-web-layout>
