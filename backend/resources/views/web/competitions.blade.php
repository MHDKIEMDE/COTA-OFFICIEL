<x-web-layout pageTitle="Compétitions — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">COUVERTURE</div>
      <h1 class="wd-h1">Compétitions</h1>
      <p class="wd-desc">Le modèle COTA couvre les grands championnats. Explorez les matchs analysés par compétition.</p>
    </div>
  </div>

  @if($competitions->isEmpty())
    <div style="text-align:center;padding:64px 32px;color:var(--dim)">
      <div style="font-family:var(--title);font-size:20px;margin-bottom:10px">Aucune compétition</div>
      <div style="font-size:13.5px">Aucune compétition active pour le moment.</div>
    </div>
  @else
    @php
      $colors = ['#0a2c5e','#11103a','#36003c','#1a1a2e','#0b3d2e','#3a1a1a','#1a2e1a','#2e1a2e'];
      $ci = 0;
    @endphp
    @foreach($competitions as $country => $comps)
    <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.18em;margin-bottom:16px;margin-top:{{ $loop->first ? 0 : 28 }}px">
      {{ strtoupper($country ?: 'MONDE') }}
    </div>
    <div class="wd-cgrid" style="margin-bottom:4px">
      @foreach($comps as $comp)
      @php $color = $colors[$ci % count($colors)]; $ci++; @endphp
      <div class="wd-panel" style="overflow:hidden">
        <div class="wd-cbanner" style="background:linear-gradient(135deg, {{ $color }}, var(--bg2))">
          <span style="font-family:var(--title);font-size:28px;color:var(--ink);letter-spacing:-.03em;opacity:.9">
            {{ strtoupper(substr($comp->name, 0, 3)) }}
          </span>
          @if($comp->is_trending ?? false)
          <span style="position:absolute;top:12px;right:12px">
            <span class="wd-pill" style="background:rgba(232,255,54,.16);color:var(--accent);font-size:10px">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="display:inline"><path d="M12 3s5 4 5 9a5 5 0 0 1-10 0c0-1.5.7-2.8 1.5-3.6C8.5 9 9 11 9 11s.5-5 3-8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
              HOT
            </span>
          </span>
          @endif
        </div>
        <div style="padding:16px">
          <div style="font-family:var(--title);font-size:16px;color:var(--ink);letter-spacing:-.02em">{{ $comp->name }}</div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.1em;margin-top:4px">{{ strtoupper($comp->country ?? $country) }}</div>
          <div style="display:flex;justify-content:space-between;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)">
            <div>
              <a href="{{ route('predictions.index', ['competition' => $comp->id]) }}" style="font-family:var(--title);font-size:18px;color:var(--ink);text-decoration:none">Voir</a>
              <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.12em;margin-top:2px">MATCHS</div>
            </div>
            <div style="text-align:right">
              <div style="font-family:var(--title);font-size:18px;color:var(--accent)">{{ $comp->priority ? 'Tier '.$comp->priority : 'Active' }}</div>
              <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.12em;margin-top:2px">NIVEAU</div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endforeach
  @endif

</x-web-layout>
