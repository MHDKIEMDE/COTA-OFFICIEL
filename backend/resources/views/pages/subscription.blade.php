@extends('layouts.app')

@php $hideDate = true; @endphp

@section('page_title', 'Premium')

@section('content')
<style>
    .sub-hero {
        margin: 16px 16px 0;
        background: linear-gradient(135deg, rgba(232,255,54,.12) 0%, rgba(232,255,54,.04) 100%);
        border: 1px solid rgba(232,255,54,.25);
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
    }
    .sub-hero__crown {
        width: 56px; height: 56px;
        background: var(--acc);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        font-size: 24px;
    }
    .sub-hero__title {
        font-family: 'Archivo', sans-serif;
        font-size: 22px; font-weight: 900;
        color: var(--ink);
        margin-bottom: 6px;
    }
    .sub-hero__sub {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px;
        color: var(--dim);
    }
    .sub-active {
        margin: 16px 16px 0;
        background: rgba(61,220,145,.08);
        border: 1px solid rgba(61,220,145,.3);
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sub-active__icon {
        width: 44px; height: 44px;
        background: rgba(61,220,145,.15);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .sub-active__label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px; font-weight: 700;
        color: var(--win);
        letter-spacing: .5px;
    }
    .sub-active__title {
        font-family: 'Archivo', sans-serif;
        font-size: 16px; font-weight: 900;
        color: var(--ink);
    }
    .sub-active__exp {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 12px;
        color: var(--dim);
        margin-top: 2px;
    }
    .sub-benefits {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 16px 16px 0;
    }
    .sub-benefit {
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .sub-benefit__icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    .sub-benefit__title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 12px; font-weight: 700;
        color: var(--ink);
    }
    .sub-benefit__desc {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 10px;
        color: var(--dim);
        margin-top: -4px;
    }
    .sub-plan {
        margin: 10px 16px 0;
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px;
        position: relative;
    }
    .sub-plan--popular {
        border-color: var(--acc);
    }
    .sub-plan__badge {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--acc);
        color: var(--bg);
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px; font-weight: 800;
        padding: 3px 12px;
        border-radius: 20px;
        letter-spacing: .5px;
    }
    .sub-plan__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .sub-plan__name {
        font-family: 'Archivo', sans-serif;
        font-size: 16px; font-weight: 900;
        color: var(--ink);
    }
    .sub-plan__dur {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11px;
        color: var(--dim);
        margin-top: 2px;
    }
    .sub-plan__price {
        font-family: 'Archivo', sans-serif;
        font-size: 26px; font-weight: 900;
        color: var(--ink);
        line-height: 1;
        text-align: right;
    }
    .sub-plan__price span {
        font-size: 12px; font-weight: 400;
        color: var(--dim);
    }
    .sub-plan__save {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px; font-weight: 700;
        color: var(--win);
        margin-top: 2px;
    }
    .sub-plan__feats {
        list-style: none;
        padding: 0; margin: 0 0 14px;
    }
    .sub-plan__feat {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 12px;
        color: var(--dim);
        margin-bottom: 6px;
    }
    .sub-plan__feat i { color: var(--win); }
    .sub-plan__btn {
        width: 100%;
        padding: 13px;
        border-radius: 10px;
        border: none;
        font-family: 'Archivo', sans-serif;
        font-size: 14px; font-weight: 900;
        cursor: pointer;
    }
    .sub-plan__btn--primary { background: var(--acc); color: var(--bg); }
    .sub-plan__btn--secondary { background: var(--bg3); border: 1px solid var(--line); color: var(--ink); }
    .sub-payments {
        display: flex;
        justify-content: center;
        gap: 16px;
        padding: 20px 16px 8px;
    }
    .sub-payment-badge {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11px;
        font-weight: 600;
        color: var(--dim);
    }
</style>

@auth
    @if(auth()->user()->is_premium ?? false)
        {{-- Statut actif --}}
        <div class="sub-active">
            <div class="sub-active__icon">⭐</div>
            <div>
                <div class="sub-active__label">ACTIF</div>
                <div class="sub-active__title">Membre Premium</div>
                <div class="sub-active__exp">
                    @if(auth()->user()->premium_expires_at ?? null)
                        Expire {{ \Carbon\Carbon::parse(auth()->user()->premium_expires_at)->locale('fr')->diffForHumans() }}
                    @else
                        Abonnement à vie ♾
                    @endif
                </div>
            </div>
        </div>
    @endif
@endauth

{{-- Hero si non-premium --}}
@auth
    @if(!(auth()->user()->is_premium ?? false))
        <div class="sub-hero">
            <div class="sub-hero__crown">⭐</div>
            <div class="sub-hero__title">Passe Premium</div>
            <div class="sub-hero__sub">Accède à tous les pronostics 3–4 étoiles</div>
        </div>
    @endif
@else
    <div class="sub-hero">
        <div class="sub-hero__crown">⭐</div>
        <div class="sub-hero__title">Passe Premium</div>
        <div class="sub-hero__sub">Accède à tous les pronostics 3–4 étoiles</div>
    </div>
@endauth

{{-- Avantages --}}
<div class="sub-benefits">
    @php
        $benefits = [
            ['icon'=>'bi-lightning-charge-fill','bg'=>'rgba(232,255,54,.10)','color'=>'var(--acc)','title'=>'Pronostics 3–4★','desc'=>'Picks à haute confiance'],
            ['icon'=>'bi-bell-fill','bg'=>'rgba(61,220,145,.10)','color'=>'var(--win)','title'=>'Alertes push','desc'=>'Notifs en temps réel'],
            ['icon'=>'bi-bar-chart-line-fill','bg'=>'rgba(61,220,145,.10)','color'=>'var(--win)','title'=>'Stats avancées','desc'=>'Tendances & ROI'],
            ['icon'=>'bi-headset','bg'=>'rgba(232,255,54,.10)','color'=>'var(--acc)','title'=>'Support VIP','desc'=>'WhatsApp dédié'],
        ];
    @endphp
    @foreach($benefits as $b)
        <div class="sub-benefit">
            <div class="sub-benefit__icon" style="background:{{ $b['bg'] }};color:{{ $b['color'] }};">
                <i class="bi {{ $b['icon'] }}"></i>
            </div>
            <div>
                <div class="sub-benefit__title">{{ $b['title'] }}</div>
                <div class="sub-benefit__desc">{{ $b['desc'] }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- Plans --}}
<p class="c-section" style="margin-top:20px;">Choisir un plan</p>

@php
    $defaultPlans = $plans ?? [
        ['key'=>'weekly','name'=>'Hebdomadaire','price'=>2000,'duration'=>'7 jours','features'=>['Pronostics 3–4 étoiles','Alertes temps réel'],'popular'=>false,'savings'=>null],
        ['key'=>'monthly','name'=>'Mensuel','price'=>5000,'duration'=>'30 jours','features'=>['Tous les pronostics','Alertes temps réel','Stats avancées','Support prioritaire'],'popular'=>true,'savings'=>'30%'],
        ['key'=>'annual','name'=>'Annuel','price'=>40000,'duration'=>'365 jours','features'=>['Tous les avantages','Badge VIP','Support WhatsApp dédié'],'popular'=>false,'savings'=>'50%'],
    ];
@endphp

@foreach($defaultPlans as $plan)
    @php $pop = is_array($plan) ? ($plan['popular'] ?? false) : ($plan->popular ?? false); @endphp
    <div class="sub-plan {{ $pop ? 'sub-plan--popular' : '' }}">
        @if($pop)
            <div class="sub-plan__badge">POPULAIRE</div>
        @endif
        <div class="sub-plan__row">
            <div>
                <div class="sub-plan__name">{{ is_array($plan) ? $plan['name'] : $plan->name }}</div>
                <div class="sub-plan__dur">{{ is_array($plan) ? $plan['duration'] : $plan->duration }}</div>
            </div>
            <div>
                <div class="sub-plan__price">
                    {{ number_format(is_array($plan) ? $plan['price'] : $plan->price, 0, ',', ' ') }}<span> FCFA</span>
                </div>
                @php $savings = is_array($plan) ? ($plan['savings'] ?? null) : ($plan->savings ?? null); @endphp
                @if($savings)
                    <div class="sub-plan__save">-{{ $savings }} ÉCONOMIE</div>
                @endif
            </div>
        </div>
        <ul class="sub-plan__feats">
            @foreach((is_array($plan) ? $plan['features'] : $plan->features) as $feat)
                <li class="sub-plan__feat">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ $feat }}
                </li>
            @endforeach
        </ul>
        @auth
            @php $planKey = is_array($plan) ? ($plan['key'] ?? $plan['id'] ?? 'plan') : ($plan->key ?? $plan->id ?? 'plan'); @endphp
            <button class="sub-plan__btn {{ $pop ? 'sub-plan__btn--primary' : 'sub-plan__btn--secondary' }}"
                    onclick="subscribe('{{ $planKey }}')">
                Choisir ce plan
            </button>
        @else
            <a href="{{ route('login') }}" class="sub-plan__btn {{ $pop ? 'sub-plan__btn--primary' : 'sub-plan__btn--secondary' }}" style="display:block;text-align:center;text-decoration:none;">
                Se connecter pour souscrire
            </a>
        @endauth
    </div>
@endforeach

{{-- Moyens de paiement --}}
<div class="sub-payments">
    @foreach($paymentChannels ?? [] as $ch)
        <span class="sub-payment-badge">{{ $ch['emoji'] ?? '' }} {{ $ch['name'] ?? '' }}</span>
    @endforeach
</div>
<p style="text-align:center;font-family:'JetBrains Mono',monospace;font-size:9px;color:var(--dim);letter-spacing:1px;padding-bottom:4px;">
    PAIEMENT SÉCURISÉ VIA MOBILE MONEY
</p>

<div style="height:16px;"></div>
@endsection

@push('scripts')
<script>
function subscribe(planKey) {
    // TODO: intégrer Paydunya
    alert('Redirection vers le paiement pour le plan : ' + planKey);
}
</script>
@endpush
