@php
    $currentDate  = request('date', now()->format('Y-m-d'));
    $selectedComp = request('competition');
    $filterConf   = request('confidence');

    $flagMap = [
        'Premier League'   => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
        'La Liga'          => '🇪🇸',
        'Serie A'          => '🇮🇹',
        'Ligue 1'          => '🇫🇷',
        'Bundesliga'       => '🇩🇪',
        'Champions League' => '⭐',
        'Europa League'    => '🟠',
        'Conference'       => '🟢',
        'CAN'              => '🏆',
        'MLS'              => '🇺🇸',
        'Brasileirao'      => '🇧🇷',
    ];
    $getFlag = fn(string $n, array $m): string => collect($m)->first(fn($v, $k) => str_contains($n, $k)) ?? '⚽';
@endphp

<x-web-layout pageTitle="Prédictions — COTA">
<style>
    .pi-comp-scroll { display:flex;gap:8px;margin-bottom:14px;overflow-x:auto;scrollbar-width:none; }
    .pi-comp-scroll::-webkit-scrollbar { display:none; }
    .pi-comp-chip { flex-shrink:0;display:flex;align-items:center;gap:6px;padding:6px 14px;background:var(--bg2);border:1px solid var(--line);border-radius:20px;text-decoration:none;color:var(--dim);font-family:var(--ui);font-size:11px;font-weight:700;white-space:nowrap;transition:all .15s; }
    .pi-comp-chip.active, .pi-comp-chip:hover { background:var(--accent);border-color:var(--accent);color:var(--bg); }
    .pi-filters { display:flex;gap:6px;margin-bottom:18px;overflow-x:auto;scrollbar-width:none; }
    .pi-filters::-webkit-scrollbar { display:none; }
    .pi-filter { flex-shrink:0;padding:5px 14px;border-radius:20px;font-family:var(--ui);font-size:11px;font-weight:700;color:var(--dim);background:var(--bg2);border:1px solid var(--line);text-decoration:none;transition:all .15s; }
    .pi-filter.active { background:var(--accent);border-color:var(--accent);color:var(--bg); }
    .pi-group-header { display:flex;align-items:center;gap:8px;padding:14px 0 8px; }
    .pi-group-name { flex:1;font-family:var(--mono);font-size:10px;font-weight:700;color:var(--dim);text-transform:uppercase;letter-spacing:.12em; }
    .pi-group-count { font-family:var(--mono);font-size:9px;color:var(--dim2); }
    .pi-card { display:block;background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:14px 16px;text-decoration:none;color:inherit;transition:border-color .15s;position:relative;overflow:hidden;margin-bottom:10px; }
    .pi-card:hover { border-color:var(--line2); }
    .pi-card--premium { border-color:rgba(232,255,54,.2); }
    .pi-card__bar { position:absolute;left:0;top:0;bottom:0;width:3px; }
    .pi-card__top { display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-left:8px; }
    .pi-card__badges { display:flex;gap:5px; }
    .pi-badge { font-family:var(--mono);font-size:8px;font-weight:700;padding:2px 7px;border-radius:4px; }
    .pi-badge--live { background:var(--loss);color:#fff; }
    .pi-badge--premium { background:rgba(232,255,54,.15);color:var(--accent);border:1px solid rgba(232,255,54,.3); }
    .pi-badge--time { background:var(--bg3);color:var(--dim); }
    .pi-teams { display:flex;align-items:center;gap:10px;padding-left:8px;margin-bottom:12px; }
    .pi-team { flex:1;display:flex;align-items:center;gap:8px;min-width:0; }
    .pi-team--away { flex-direction:row-reverse; }
    .pi-team__logo { width:30px;height:30px;border-radius:6px;object-fit:contain;background:var(--bg3);flex-shrink:0; }
    .pi-team__logo--ph { display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:7px;font-weight:800;color:var(--dim);border:1px solid var(--line); }
    .pi-team__name { font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .pi-vsbox { display:flex;flex-direction:column;align-items:center;gap:2px;flex-shrink:0; }
    .pi-vsbox__score { font-family:var(--title);font-size:20px;color:var(--ink);letter-spacing:-1px;line-height:1; }
    .pi-vsbox__time { font-family:var(--mono);font-size:10px;font-weight:700;color:var(--dim); }
    .pi-vsbox__time--live { color:var(--loss); }
    .pi-pick { display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--bg3);border-radius:9px;margin-left:8px; }
    .pi-pick__type { font-family:var(--mono);font-size:8px;font-weight:700;color:var(--dim);letter-spacing:.5px;text-transform:uppercase;margin-right:8px; }
    .pi-pick__val { font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink); }
    .pi-pick__odds { font-family:var(--title);font-size:16px;color:var(--accent); }
    .pi-pick__stars { display:flex;gap:1px; }
    .pi-pick__star { font-size:10px;color:var(--accent); }
    .pi-pick__star--off { color:var(--line2); }
    .pi-lock { display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;background:rgba(232,255,54,.04);border-radius:9px;border:1px dashed rgba(232,255,54,.2);margin-left:8px; }
    .pi-lock__text { font-family:var(--ui);font-size:12px;font-weight:600;color:var(--accent); }
</style>

  <div class="wd-topbar">
    <div>
      <div class="wd-date">{{ strtoupper(now()->locale('fr')->isoFormat('ddd. D MMM YYYY')) }}</div>
      <h1 class="wd-h1">Prédictions</h1>
    </div>
    <div class="wd-topactions">
      <a href="{{ route('predictions.index') }}" class="wd-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Aujourd'hui
      </a>
    </div>
  </div>

  {{-- Carousel compétitions --}}
  @if(!empty($competitions ?? []))
  <div class="pi-comp-scroll">
    <a href="{{ route('predictions.index', ['date'=>$currentDate]) }}" class="pi-comp-chip {{ !$selectedComp ? 'active' : '' }}">⚽ Toutes</a>
    @foreach($competitions as $comp)
      @php $compName = is_array($comp) ? ($comp['name'] ?? '') : ($comp->name ?? ''); @endphp
      <a href="{{ route('predictions.index', ['date'=>$currentDate,'competition'=>is_array($comp)?($comp['id']??$compName):($comp->id??$compName)]) }}"
         class="pi-comp-chip {{ $selectedComp == (is_array($comp)?($comp['id']??$compName):($comp->id??$compName)) ? 'active' : '' }}">
        {{ $getFlag($compName, $flagMap) }} {{ $compName }}
      </a>
    @endforeach
  </div>
  @endif

  {{-- Filtres --}}
  <div class="pi-filters">
    <a href="{{ route('predictions.index', ['date'=>$currentDate]) }}" class="pi-filter {{ !$filterConf && !$selectedComp ? 'active' : '' }}">Tous</a>
    <a href="{{ route('predictions.index', ['date'=>$currentDate,'confidence'=>70]) }}" class="pi-filter {{ $filterConf == 70 ? 'active' : '' }}">🔥 Top picks</a>
    <a href="{{ route('predictions.index', ['date'=>$currentDate,'confidence'=>80]) }}" class="pi-filter {{ $filterConf == 80 ? 'active' : '' }}">⭐ Premium</a>
  </div>

  @php $grouped = isset($predictions) ? $predictions->groupBy('competition') : collect([]); @endphp

  @forelse($grouped as $competition => $matches)
    <div>
      <div class="pi-group-header">
        @php $firstM = $matches->first(); @endphp
        @if($firstM->competition_logo ?? null)
          <img src="{{ $firstM->competition_logo }}" alt="" style="width:18px;height:18px;border-radius:3px;object-fit:contain;">
        @else
          <span style="font-size:16px;">{{ $getFlag($competition, $flagMap) }}</span>
        @endif
        <span class="pi-group-name">{{ $competition }}</span>
        <span class="pi-group-count">{{ $matches->count() }}</span>
      </div>

      @foreach($matches as $prediction)
        @php
          $isLive    = ($prediction->status ?? '') === 'live';
          $isDone    = in_array($prediction->status ?? '', ['won','lost','finished','cancelled']);
          $isPremium = ($prediction->is_premium ?? false) || (($prediction->confidence_stars ?? 0) >= 3);
          $canSee    = !$isPremium || (auth()->check() && (auth()->user()->is_premium ?? false));
          $stars     = $prediction->confidence_stars ?? 1;
          $odds      = $prediction->odds ?? $prediction->estimated_odds ?? null;
          $outcome   = $prediction->predicted_outcome ?? $prediction->prediction ?? null;
          $betType   = $prediction->bet_type ?? $prediction->prediction_type ?? null;
          $matchDate = isset($prediction->match_date) ? \Carbon\Carbon::parse($prediction->match_date) : null;
          $barColor  = $isDone
            ? (($prediction->result ?? $prediction->status ?? '') === 'won' ? 'var(--win)' : 'var(--loss)')
            : ($isPremium ? 'var(--accent)' : 'var(--line)');
        @endphp
        <a href="{{ route('predictions.show', $prediction->id) }}" class="pi-card {{ $isPremium ? 'pi-card--premium' : '' }}">
          <div class="pi-card__bar" style="background:{{ $barColor }};"></div>
          <div class="pi-card__top">
            <span style="font-family:var(--mono);font-size:10px;color:var(--dim);">{{ $firstM->country ?? $competition }}</span>
            <div class="pi-card__badges">
              @if($isPremium)<span class="pi-badge pi-badge--premium">⭐ PREMIUM</span>@endif
              @if($isLive)
                <span class="pi-badge pi-badge--live">● LIVE</span>
              @elseif(!$isDone && $matchDate)
                <span class="pi-badge pi-badge--time">{{ $matchDate->format('H:i') }}</span>
              @endif
            </div>
          </div>
          <div class="pi-teams">
            <div class="pi-team">
              @if($prediction->home_team_logo ?? null)
                <img src="{{ $prediction->home_team_logo }}" alt="" class="pi-team__logo">
              @else
                <div class="pi-team__logo pi-team__logo--ph">{{ strtoupper(substr($prediction->home_team ?? '?', 0, 3)) }}</div>
              @endif
              <span class="pi-team__name">{{ $prediction->home_team ?? '—' }}</span>
            </div>
            <div class="pi-vsbox">
              @if($isLive)
                <span class="pi-vsbox__score">{{ $prediction->home_score ?? 0 }}-{{ $prediction->away_score ?? 0 }}</span>
                <span class="pi-vsbox__time pi-vsbox__time--live">{{ $prediction->live_minute ?? '?' }}'</span>
              @elseif($isDone && isset($prediction->home_score))
                <span class="pi-vsbox__score">{{ $prediction->home_score }}-{{ $prediction->away_score }}</span>
                <span class="pi-vsbox__time">FT</span>
              @else
                <span class="pi-vsbox__time">vs</span>
              @endif
            </div>
            <div class="pi-team pi-team--away">
              @if($prediction->away_team_logo ?? null)
                <img src="{{ $prediction->away_team_logo }}" alt="" class="pi-team__logo">
              @else
                <div class="pi-team__logo pi-team__logo--ph">{{ strtoupper(substr($prediction->away_team ?? '?', 0, 3)) }}</div>
              @endif
              <span class="pi-team__name">{{ $prediction->away_team ?? '—' }}</span>
            </div>
          </div>
          @if($canSee && $outcome)
            <div class="pi-pick">
              <div style="display:flex;align-items:center;gap:6px;">
                @if($betType)<span class="pi-pick__type">{{ strtoupper($betType) }}</span>@endif
                <span class="pi-pick__val">{{ $outcome }}</span>
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                @if($odds)<span class="pi-pick__odds">@{{ number_format($odds, 2) }}</span>@endif
                <div class="pi-pick__stars">
                  @for($s = 1; $s <= 4; $s++)<span class="pi-pick__star {{ $s <= $stars ? '' : 'pi-pick__star--off' }}">★</span>@endfor
                </div>
              </div>
            </div>
          @else
            <div class="pi-lock">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:var(--accent)"><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
              <span class="pi-lock__text">Premium — Débloquer →</span>
            </div>
          @endif
        </a>
      @endforeach
    </div>
  @empty
    <div style="text-align:center;padding:64px 32px;color:var(--dim)">
      <div style="font-family:var(--title);font-size:20px;color:var(--ink);margin-bottom:10px">Aucun pronostic</div>
      <div style="font-size:13.5px">Pas de pronostics disponibles pour cette date.</div>
      <a href="{{ route('predictions.index') }}" style="display:inline-block;margin-top:16px;padding:10px 22px;background:var(--bg2);border:1px solid var(--line);border-radius:10px;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink);text-decoration:none;">← Voir aujourd'hui</a>
    </div>
  @endforelse

</x-web-layout>
