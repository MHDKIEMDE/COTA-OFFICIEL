<x-web-layout pageTitle="Compétitions — COTA">
<style>
    .cmp-trending { display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:24px; }
    .cmp-trending-card { display:flex;align-items:center;gap:10px;background:var(--bg2);border:1px solid var(--line);border-radius:14px;padding:14px;text-decoration:none;color:inherit;transition:border-color .15s; }
    .cmp-trending-card:hover { border-color:var(--accent); }
    .cmp-trending-card--hot { border-color:rgba(232,255,54,.25); }
    .cmp-trending-card__flag { font-size:22px; }
    .cmp-trending-card__name { font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink); }
    .cmp-trending-card__count { font-family:var(--mono);font-size:9px;color:var(--dim);margin-top:2px; }
    .cmp-country-label { font-family:var(--mono);font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--dim);padding:18px 0 8px; }
    .cmp-section-wrap { background:var(--bg2);border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:18px; }
    .cmp-row { display:flex;align-items:center;gap:12px;padding:13px 16px;border-bottom:1px solid var(--line2);text-decoration:none;color:inherit;transition:background .1s; }
    .cmp-row:last-child { border-bottom:none; }
    .cmp-row:hover { background:var(--bg3); }
    .cmp-row__icon { font-size:20px;flex-shrink:0; }
    .cmp-row__logo { width:28px;height:28px;border-radius:4px;object-fit:contain;flex-shrink:0; }
    .cmp-row__name { flex:1;font-family:var(--ui);font-size:13px;font-weight:700;color:var(--ink); }
    .cmp-row__sub { font-family:var(--ui);font-size:10px;color:var(--dim);margin-top:2px; }
    .cmp-row__badge { font-family:var(--mono);font-size:8px;font-weight:700;padding:2px 7px;border-radius:4px; }
    @media(max-width:960px){ .cmp-trending { grid-template-columns:1fr 1fr; } }
</style>

  <div class="wd-topbar">
    <div>
      <div class="wd-date">COUVERTURE</div>
      <h1 class="wd-h1">Compétitions</h1>
    </div>
  </div>

  {{-- Trending --}}
  <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.18em;margin-bottom:14px">🔥 TENDANCES</div>
  @php
    $trending = [
      ['name'=>'CAN 2026','icon'=>'🏆','matches'=>8,'hot'=>true],
      ['name'=>'Premier League','icon'=>'🏴󠁧󠁢󠁥󠁮󠁧󠁿','matches'=>10,'hot'=>false],
      ['name'=>'Champions League','icon'=>'⭐','matches'=>8,'hot'=>false],
      ['name'=>'La Liga','icon'=>'🇪🇸','matches'=>10,'hot'=>false],
    ];
  @endphp
  <div class="cmp-trending">
    @foreach($trending as $t)
      <a href="{{ route('home', ['competition' => $t['name']]) }}" class="cmp-trending-card {{ $t['hot'] ? 'cmp-trending-card--hot' : '' }}">
        <span class="cmp-trending-card__flag">{{ $t['icon'] }}</span>
        <div>
          <div class="cmp-trending-card__name">{{ $t['name'] }}</div>
          <div class="cmp-trending-card__count">{{ $t['matches'] }} matchs</div>
        </div>
      </a>
    @endforeach
  </div>

  {{-- Liste par pays --}}
  @php
    $defaultCompetitions = $competitions ?? collect([
      'Europe'    => collect([(object)['name'=>'Champions League','full_name'=>'UEFA Champions League','icon'=>'⭐','logo'=>null,'is_trending'=>false],(object)['name'=>'Europa League','full_name'=>'UEFA Europa League','icon'=>'🟠','logo'=>null,'is_trending'=>false],(object)['name'=>'Conf. League','full_name'=>'UEFA Conference League','icon'=>'🟢','logo'=>null,'is_trending'=>false]]),
      'Angleterre'=> collect([(object)['name'=>'Premier League','full_name'=>'English Premier League','icon'=>'🏴󠁧󠁢󠁥󠁮󠁧󠁿','logo'=>null,'is_trending'=>false]]),
      'Espagne'   => collect([(object)['name'=>'La Liga','full_name'=>'La Liga Santander','icon'=>'🇪🇸','logo'=>null,'is_trending'=>false]]),
      'Italie'    => collect([(object)['name'=>'Serie A','full_name'=>'Serie A TIM','icon'=>'🇮🇹','logo'=>null,'is_trending'=>false]]),
      'France'    => collect([(object)['name'=>'Ligue 1','full_name'=>"Ligue 1 McDonald's",'icon'=>'🇫🇷','logo'=>null,'is_trending'=>false]]),
      'Allemagne' => collect([(object)['name'=>'Bundesliga','full_name'=>'Deutsche Bundesliga','icon'=>'🇩🇪','logo'=>null,'is_trending'=>false]]),
      'Afrique'   => collect([(object)['name'=>'CAN 2026','full_name'=>"Coupe d'Afrique des Nations",'icon'=>'🌍','logo'=>null,'is_trending'=>true],(object)['name'=>'CAF CL','full_name'=>'CAF Champions League','icon'=>'🌍','logo'=>null,'is_trending'=>false]]),
    ]);
  @endphp

  @foreach($defaultCompetitions as $country => $comps)
    <div class="cmp-country-label">{{ $country }}</div>
    <div class="cmp-section-wrap">
      @foreach($comps as $comp)
        <a href="{{ route('home', ['competition' => $comp->name ?? '']) }}" class="cmp-row">
          @if($comp->logo ?? null)<img src="{{ $comp->logo }}" alt="" class="cmp-row__logo">
          @else<span class="cmp-row__icon">{{ $comp->icon ?? '⚽' }}</span>@endif
          <div style="flex:1;min-width:0;">
            <div class="cmp-row__name">{{ $comp->name ?? '—' }}</div>
            @if(isset($comp->full_name) && $comp->full_name !== ($comp->name ?? ''))<div class="cmp-row__sub">{{ $comp->full_name }}</div>@endif
          </div>
          @if($comp->is_trending ?? false)
            <span class="cmp-row__badge" style="background:rgba(255,91,58,.12);color:var(--loss);">🔥 HOT</span>
          @endif
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:var(--dim);flex-shrink:0"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      @endforeach
    </div>
  @endforeach

</x-web-layout>
