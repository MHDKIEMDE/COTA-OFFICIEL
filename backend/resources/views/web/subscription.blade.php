<x-web-layout pageTitle="Abonnement — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOTRE PLAN</div>
      <h1 class="wd-h1">Abonnement</h1>
      <p class="wd-desc">Choisissez la formule qui correspond à votre pratique. Sans engagement, résiliable à tout moment.</p>
    </div>
  </div>

  <div class="wd-plans">
    @foreach($plans as $plan)
    @php
      $isHot = $plan['popular'] ?? false;
      $isCurrent = $currentSubscription && ($currentSubscription->plan_type === $plan['key']);
      $price = is_numeric($plan['price']) ? number_format($plan['price'], 0, ',', ' ') : $plan['price'];
    @endphp
    <div class="wd-panel wd-plan {{ $isHot ? 'hot' : '' }}">
      @if($isHot)
      <div class="wd-plan-ribbon">POPULAIRE</div>
      @endif
      <div style="font-family:var(--title);font-size:20px;color:{{ $isHot ? 'var(--accent)' : 'var(--ink)' }};letter-spacing:-.02em">{{ $plan['name'] }}</div>
      <div style="font-size:12.5px;color:var(--dim);margin-top:4px">{{ $plan['duration'] }}</div>
      <div class="wd-plan-price">
        <span class="wd-plan-price-main">{{ $price }}</span>
        <span class="wd-plan-price-per">FCFA</span>
      </div>
      <div class="wd-plan-features">
        @foreach($plan['features'] as $feature)
        <div class="wd-plan-feat">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;color:{{ $isHot ? 'var(--accent)' : 'var(--win)' }}"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ $feature }}
        </div>
        @endforeach
      </div>
      @if($isCurrent)
        <button class="wd-plan-btn current" disabled>Plan actuel</button>
      @elseif($isHot)
        <a href="#" class="wd-plan-btn cta">S'abonner</a>
      @else
        <a href="#" class="wd-plan-btn">Choisir ce plan</a>
      @endif
    </div>
    @endforeach
  </div>

  {{-- Moyens de paiement --}}
  <div style="margin-top:32px">
    <div class="wd-panel" style="padding:22px">
      <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.16em;margin-bottom:18px">MOYENS DE PAIEMENT ACCEPTÉS</div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
        @foreach($paymentChannels as $channel)
        <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--bg3);border:1px solid var(--line);border-radius:11px">
          <span style="font-size:18px">{{ $channel['emoji'] ?? '' }}</span>
          <span style="font-family:var(--ui);font-size:13px;font-weight:600;color:var(--ink)">{{ $channel['name'] }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</x-web-layout>
