<x-web-layout pageTitle="Live — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">EN DIRECT</div>
      <h1 class="wd-h1">Matchs en live</h1>
      <p class="wd-desc">Suivez l'évolution de vos prédictions minute par minute.</p>
    </div>
    <div class="wd-topactions">
      <span class="wd-badge" style="color:var(--loss);background:rgba(255,91,58,.16)">
        <span class="wd-livedot"></span>{{ $liveCount }} EN COURS
      </span>
    </div>
  </div>

  @if($liveMatches->isEmpty() && $upcomingMatches->isEmpty())
    <div style="text-align:center;padding:64px 32px;color:var(--dim)">
      <div style="font-family:var(--title);font-size:20px;margin-bottom:10px">Aucun match en direct</div>
      <div style="font-size:13.5px">Aucun match live en ce moment. Revenez plus tard.</div>
    </div>
  @else
    @if($liveMatches->isNotEmpty())
    <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.18em;margin-bottom:16px">MATCHS EN COURS</div>
    <div class="wd-livegrid">
      @foreach($liveMatches as $pred)
      @php
        $conf = round($pred->confidence ?? 0);
        $statusColor = $pred->status === 'live' ? 'var(--loss)' : 'var(--cool)';
      @endphp
      <div class="wd-panel wd-livecard">
        <div style="padding:16px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)">
          <span class="wd-badge" style="color:var(--loss);background:rgba(255,91,58,.16)">
            <span class="wd-livedot"></span>
            LIVE · {{ $pred->match_time ? substr($pred->match_time,0,5) : '—' }}
          </span>
          <span style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.1em">{{ strtoupper(Str::limit($pred->competition ?? '', 18)) }}</span>
        </div>
        <div style="padding:20px 18px;display:flex;align-items:center;justify-content:center;gap:18px">
          <div style="text-align:right">
            <div style="font-family:var(--title);font-size:16px;color:var(--ink)">{{ $pred->home_team ?? '?' }}</div>
          </div>
          <div style="font-family:var(--title);font-size:30px;color:var(--ink);letter-spacing:-.03em">
            {{ $pred->home_score ?? '0' }} – {{ $pred->away_score ?? '0' }}
          </div>
          <div style="text-align:left">
            <div style="font-family:var(--title);font-size:16px;color:var(--ink)">{{ $pred->away_team ?? '?' }}</div>
          </div>
        </div>
        <div style="padding:0 18px 18px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div>
              <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.14em">VOTRE PRÉDICTION</div>
              <div style="font-size:13.5px;color:var(--ink);font-weight:600;margin-top:4px">{{ $pred->prediction_type ?? $pred->bet_type ?? '—' }}</div>
            </div>
            <span class="wd-badge" style="color:var(--cool);background:rgba(111,180,217,.1);white-space:nowrap">En cours</span>
          </div>
          @if($conf > 0)
          <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.14em;margin-bottom:6px">CONFIANCE</div>
          <div style="height:8px;border-radius:4px;overflow:hidden;background:var(--bg3)">
            <div style="width:{{ $conf }}%;height:100%;background:var(--accent);border-radius:4px"></div>
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
    @endif

    @if($upcomingMatches->isNotEmpty())
    <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.18em;margin:28px 0 16px">À VENIR AUJOURD'HUI</div>
    <div class="wd-livegrid">
      @foreach($upcomingMatches as $pred)
      <div class="wd-panel" style="opacity:.8">
        <div style="padding:16px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)">
          <span class="wd-pill" style="background:rgba(111,180,217,.1);color:var(--cool);font-size:10px">
            {{ $pred->match_time ? substr($pred->match_time,0,5) : '—' }}
          </span>
          <span style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.1em">{{ strtoupper(Str::limit($pred->competition ?? '', 18)) }}</span>
        </div>
        <div style="padding:16px 18px">
          <div style="font-family:var(--title);font-size:16px;color:var(--ink);text-align:center">
            {{ $pred->home_team ?? '?' }} <span style="color:var(--dim2)">vs</span> {{ $pred->away_team ?? '?' }}
          </div>
          @if($pred->prediction_type ?? $pred->bet_type)
          <div style="margin-top:12px;font-size:13px;color:var(--ink2);text-align:center">{{ $pred->prediction_type ?? $pred->bet_type }}</div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
    @endif
  @endif

</x-web-layout>
