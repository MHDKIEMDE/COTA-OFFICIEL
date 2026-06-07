<x-web-layout pageTitle="Prédictions — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">{{ strtoupper(now()->locale('fr')->isoFormat('ddd. D MMM YYYY')) }}</div>
      <h1 class="wd-h1">Prédictions du jour</h1>
      <p class="wd-desc">Chaque match est analysé selon 9 critères. Le score de confiance résume la conviction du modèle COTA.</p>
    </div>
    <div class="wd-topactions">
      <a href="#" class="wd-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Combiné du jour
      </a>
    </div>
  </div>

  <form method="GET" action="{{ route('predictions.index') }}" class="wd-filterbar">
    <div class="wd-search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="var(--dim)" stroke-width="1.7"/><path d="m20 20-3.2-3.2" stroke="var(--dim)" stroke-width="1.7" stroke-linecap="round"/></svg>
      <input type="text" name="q" placeholder="Rechercher un match, une équipe…" value="{{ request('q') }}">
    </div>
    <div class="wd-filter" style="cursor:default">
      <span class="wd-filter-label">COMPÉTITION</span>
      <div class="wd-filter-val">
        <select name="competition" style="background:transparent;border:none;outline:none;color:var(--ink);font-family:var(--ui);font-size:13px;font-weight:500;width:100%;cursor:pointer;-webkit-appearance:none">
          <option value="">Toutes</option>
          @foreach($competitions as $comp)
          <option value="{{ $comp['id'] }}" {{ $filters['competition'] == $comp['id'] ? 'selected' : '' }}>{{ $comp['name'] }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="wd-filter" style="cursor:default">
      <span class="wd-filter-label">DATE</span>
      <div class="wd-filter-val">
        <input type="date" name="date" value="{{ $filters['date'] }}" style="background:transparent;border:none;outline:none;color:var(--ink);font-family:var(--ui);font-size:13px;font-weight:500;width:100%;cursor:pointer;-webkit-appearance:none">
      </div>
    </div>
    <div class="wd-filter" style="cursor:default">
      <span class="wd-filter-label">CONFIANCE MIN.</span>
      <div class="wd-filter-val">
        <select name="confidence" style="background:transparent;border:none;outline:none;color:var(--ink);font-family:var(--ui);font-size:13px;font-weight:500;width:100%;cursor:pointer;-webkit-appearance:none">
          <option value="">Tous niveaux</option>
          <option value="50" {{ $filters['confidence'] == 50 ? 'selected' : '' }}>50 %+</option>
          <option value="60" {{ $filters['confidence'] == 60 ? 'selected' : '' }}>60 %+</option>
          <option value="70" {{ $filters['confidence'] == 70 ? 'selected' : '' }}>70 %+</option>
          <option value="85" {{ $filters['confidence'] == 85 ? 'selected' : '' }}>85 %+ (Premium)</option>
        </select>
      </div>
    </div>
    <button type="submit" class="wd-filterbtn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 5h18l-7 8v6l-4-2v-4z" stroke="var(--bg)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Filtrer
    </button>
  </form>

  @if($predictions->isEmpty())
    <div style="text-align:center;padding:64px 32px;color:var(--dim)">
      <div style="font-family:var(--title);font-size:20px;margin-bottom:10px">Aucune prédiction</div>
      <div style="font-size:13.5px">Aucun pronostic pour cette date ou ces filtres.</div>
    </div>
  @else
  <div class="wd-pgrid">
    @foreach($predictions as $pred)
    @php
      $conf = round($pred->confidence ?? 0);
      $confColor = $conf >= 85 ? 'var(--accent)' : ($conf >= 70 ? 'var(--cool)' : 'var(--dim)');
      $valueLabel = $conf >= 85 ? 'ÉLEVÉE' : ($conf >= 70 ? 'MOYENNE' : 'FAIBLE');
      $valueBg = $conf >= 85 ? 'var(--accent-dim)' : ($conf >= 70 ? 'var(--cool-dim)' : 'rgba(255,255,255,.05)');
      $valueColor = $conf >= 85 ? 'var(--accent)' : ($conf >= 70 ? 'var(--cool)' : 'var(--dim)');
      $isLive = $pred->status === 'live';
      $circ = round(2*3.14159*21,1);
      $arc = round(2*3.14159*21*$conf/100,1);
    @endphp
    <a href="{{ route('predictions.show', $pred->id) }}" class="wd-pcard">
      <div class="wd-pcard-back">
        <div style="position:absolute;top:12px;left:12px;right:12px;display:flex;justify-content:space-between;align-items:center">
          <span class="wd-pill" style="background:rgba(11,13,16,.62);color:var(--ink);border:1px solid var(--line2);font-size:9.5px">{{ strtoupper(Str::limit($pred->competition ?? '', 20)) }}</span>
          @if($isLive)
            <span class="wd-badge" style="color:var(--loss);background:rgba(255,91,58,.16)"><span class="wd-livedot"></span>LIVE</span>
          @else
            <span class="wd-pill" style="background:rgba(11,13,16,.62);color:var(--ink2);border:1px solid var(--line2);font-size:9.5px">
              {{ $pred->match_date ? \Carbon\Carbon::parse($pred->match_date)->format('d/m') : '' }} {{ $pred->match_time ? substr($pred->match_time,0,5) : '' }}
            </span>
          @endif
        </div>
        <div style="position:absolute;bottom:14px;left:14px;right:14px;display:flex;align-items:center;gap:10px">
          <div style="flex:1;font-family:var(--title);font-size:17px;color:var(--ink);letter-spacing:-.02em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            {{ $pred->home_team ?? '?' }} <span style="color:var(--dim2);font-size:12px">vs</span> {{ $pred->away_team ?? '?' }}
          </div>
        </div>
      </div>
      <div class="wd-pcard-body">
        <div style="display:flex;align-items:center;gap:14px">
          <div style="position:relative;width:52px;height:52px;flex-shrink:0">
            <svg viewBox="0 0 52 52" width="52" height="52" style="transform:rotate(-90deg)">
              <circle cx="26" cy="26" r="21" fill="none" stroke="var(--bg3)" stroke-width="4.5"/>
              <circle cx="26" cy="26" r="21" fill="none" stroke="{{ $confColor }}" stroke-width="4.5" stroke-dasharray="{{ $arc }} {{ $circ }}" stroke-linecap="round"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:12px;font-weight:700;color:{{ $confColor }}">{{ $conf }}</div>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.14em">CONSEIL IA</div>
            <div style="font-size:14.5px;color:var(--ink);font-weight:600;margin-top:4px">{{ $pred->prediction_type ?? $pred->bet_type ?? 'Analyse disponible' }}</div>
            <div style="margin-top:8px"><span class="wd-pill" style="background:{{ $valueBg }};color:{{ $valueColor }};font-size:10px">VALUE {{ $valueLabel }}</span></div>
          </div>
          @if($pred->odds)
          <div style="flex-shrink:0">
            <div style="font-family:var(--mono);font-size:16px;font-weight:700;padding:5px 10px;border-radius:8px;white-space:nowrap;color:{{ $conf >= 85 ? 'var(--accent)' : 'var(--ink)' }};background:{{ $conf >= 85 ? 'var(--accent-dim)' : 'rgba(255,255,255,.06)' }};border:1px solid {{ $conf >= 85 ? 'rgba(232,255,54,.3)' : 'var(--line2)' }}">
              @{{ number_format($pred->odds,2) }}
            </div>
          </div>
          @endif
        </div>
        <div class="wd-pcard-foot">
          <span style="font-family:var(--mono);font-size:10.5px;color:var(--dim);letter-spacing:.08em">9 CRITÈRES ANALYSÉS</span>
          <span style="font-family:var(--mono);font-size:11px;color:var(--accent);font-weight:600;display:inline-flex;align-items:center;gap:4px">
            Voir l'analyse <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
        </div>
      </div>
    </a>
    @endforeach
  </div>
  @endif

</x-web-layout>
