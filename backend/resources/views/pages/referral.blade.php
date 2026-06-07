@extends('layouts.app')

@php $hideDate = true; @endphp

@section('page_title', 'Parrainage')

@section('content')
<style>
    .ref-hero {
        margin: 16px;
        background: linear-gradient(135deg, var(--acc) 0%, #c5d400 100%);
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
    }
    .ref-hero__icon {
        width: 56px; height: 56px;
        background: rgba(0,0,0,.12);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        font-size: 24px;
    }
    .ref-hero__title {
        font-family: 'Archivo', sans-serif;
        font-size: 22px; font-weight: 900;
        color: var(--bg);
        margin-bottom: 6px;
    }
    .ref-hero__sub {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px;
        color: rgba(11,13,16,.65);
    }
    .ref-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        padding: 0 16px 16px;
    }
    .ref-stat {
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 14px 10px;
        text-align: center;
    }
    .ref-stat__val {
        font-family: 'Archivo', sans-serif;
        font-size: 24px; font-weight: 900;
        line-height: 1;
    }
    .ref-stat__label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 8px;
        color: var(--dim);
        letter-spacing: 1px;
        margin-top: 4px;
    }
    .ref-code-box {
        margin: 0 16px;
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px;
    }
    .ref-code-label {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 10px;
        color: var(--dim);
        margin-bottom: 8px;
        letter-spacing: .5px;
        text-transform: uppercase;
    }
    .ref-code-row {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg3);
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 14px;
    }
    .ref-code-val {
        flex: 1;
        font-family: 'JetBrains Mono', monospace;
        font-size: 20px; font-weight: 800;
        letter-spacing: .15em;
        color: var(--acc);
    }
    .ref-code-copy {
        padding: 8px 16px;
        background: var(--acc);
        border: none;
        border-radius: 8px;
        font-family: 'Archivo', sans-serif;
        font-size: 12px; font-weight: 900;
        color: var(--bg);
        cursor: pointer;
        display: flex; align-items: center; gap: 5px;
    }
    .ref-share-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .ref-share-btn {
        padding: 11px;
        border-radius: 9px;
        border: 1px solid var(--line);
        font-family: 'Space Grotesk', sans-serif;
        font-size: 12px; font-weight: 700;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .ref-share-btn--wa { background: #25D366; border-color: #25D366; color: #fff; }
    .ref-share-btn--share { background: var(--bg3); color: var(--ink); }
    .ref-steps {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding: 0 16px;
    }
    .ref-step {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid var(--line2);
    }
    .ref-step:last-child { border-bottom: none; }
    .ref-step__num {
        width: 32px; height: 32px;
        background: var(--acc);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Archivo', sans-serif;
        font-size: 14px; font-weight: 900;
        color: var(--bg);
        flex-shrink: 0;
    }
    .ref-step__title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700;
        color: var(--ink);
    }
    .ref-step__desc {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11px;
        color: var(--dim);
        margin-top: 2px;
    }
    .ref-list-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line2);
    }
    .ref-list-row:last-child { border-bottom: none; }
    .ref-list-avatar {
        width: 38px; height: 38px;
        background: var(--bg3);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ref-list-name {
        flex: 1;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 600;
        color: var(--ink);
    }
    .ref-list-date {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        color: var(--dim);
        margin-top: 2px;
    }
    .ref-list-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px; font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
    }
</style>

@auth
    {{-- Hero --}}
    <div class="ref-hero">
        <div class="ref-hero__icon">🎁</div>
        <div class="ref-hero__title">Gagne 7 jours Premium !</div>
        <div class="ref-hero__sub">Pour chaque ami qui s'inscrit avec ton code</div>
    </div>

    {{-- Stats --}}
    <div class="ref-stats">
        <div class="ref-stat">
            <div class="ref-stat__val" style="color:var(--acc);">{{ $stats['total_referrals'] ?? 0 }}</div>
            <div class="ref-stat__label">Filleuls</div>
        </div>
        <div class="ref-stat">
            <div class="ref-stat__val" style="color:var(--win);">{{ $stats['premium_days_earned'] ?? 0 }}</div>
            <div class="ref-stat__label">Jours gagnés</div>
        </div>
        <div class="ref-stat">
            <div class="ref-stat__val" style="color:var(--dim);">{{ $stats['pending_rewards'] ?? 0 }}</div>
            <div class="ref-stat__label">En attente</div>
        </div>
    </div>

    {{-- Code --}}
    <div class="ref-code-box">
        <div class="ref-code-label">Ton code de parrainage</div>
        @php
            $referralCode = auth()->user()->referral_code
                ?? strtoupper(substr(md5((string) auth()->user()->id), 0, 8));
        @endphp
        <div class="ref-code-row">
            <span class="ref-code-val" id="referralCode">{{ $referralCode }}</span>
            <button class="ref-code-copy" onclick="copyCode()">
                <i class="bi bi-copy"></i> Copier
            </button>
        </div>
        <div class="ref-share-row">
            <button class="ref-share-btn ref-share-btn--wa" onclick="shareWhatsApp()">
                <i class="bi bi-whatsapp"></i> WhatsApp
            </button>
            <button class="ref-share-btn ref-share-btn--share" onclick="shareGeneric()">
                <i class="bi bi-share"></i> Partager
            </button>
        </div>
    </div>

    {{-- Comment ça marche --}}
    <p class="c-section" style="margin-top:20px;">Comment ça marche ?</p>
    <div style="background:var(--bg2);border:1px solid var(--line);border-radius:10px;margin:0 16px;overflow:hidden;">
        <div class="ref-steps">
            <div class="ref-step">
                <div class="ref-step__num">1</div>
                <div>
                    <div class="ref-step__title">Partage ton code</div>
                    <div class="ref-step__desc">Envoie ton code à tes amis via WhatsApp ou SMS</div>
                </div>
            </div>
            <div class="ref-step">
                <div class="ref-step__num">2</div>
                <div>
                    <div class="ref-step__title">Ton ami s'inscrit</div>
                    <div class="ref-step__desc">Il entre ton code lors de l'inscription sur COTA</div>
                </div>
            </div>
            <div class="ref-step">
                <div class="ref-step__num">3</div>
                <div>
                    <div class="ref-step__title">Tu gagnes 7 jours</div>
                    <div class="ref-step__desc">Ton compte Premium est crédité automatiquement</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filleuls --}}
    @if(($referrals ?? collect())->isNotEmpty())
        <p class="c-section" style="margin-top:20px;">Mes filleuls</p>
        <div style="background:var(--bg2);border:1px solid var(--line);border-radius:10px;margin:0 16px;overflow:hidden;">
            @foreach($referrals as $ref)
                @php
                    $done = ($ref['status'] ?? '') === 'completed';
                @endphp
                <div class="ref-list-row">
                    <div class="ref-list-avatar">
                        <i class="bi bi-person" style="color:var(--dim);font-size:16px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="ref-list-name">{{ $ref['name'] ?? 'Utilisateur' }}</div>
                        <div class="ref-list-date">
                            {{ isset($ref['created_at']) ? \Carbon\Carbon::parse($ref['created_at'])->locale('fr')->diffForHumans() : '—' }}
                        </div>
                    </div>
                    <span class="ref-list-badge" style="{{ $done ? 'background:rgba(61,220,145,.15);color:var(--win);' : 'background:var(--bg3);color:var(--dim);' }}">
                        {{ $done ? '+7j' : 'EN ATTENTE' }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

@else
    <div class="c-empty" style="min-height:65vh;">
        <div class="c-empty__icon"><i class="bi bi-gift"></i></div>
        <div class="c-empty__title">Programme de parrainage</div>
        <div class="c-empty__sub">Connecte-toi pour accéder à ton code et gagner des jours Premium.</div>
        <a href="{{ route('login') }}" style="margin-top:12px;padding:12px 28px;background:var(--acc);border-radius:10px;font-family:'Archivo',sans-serif;font-size:14px;font-weight:900;color:var(--bg);text-decoration:none;">
            Se connecter →
        </a>
    </div>
@endauth

<div style="height:16px;"></div>
@endsection

@push('scripts')
<script>
function copyCode() {
    const code = document.getElementById('referralCode').textContent.trim();
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Copié !';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
function shareWhatsApp() {
    const code = document.getElementById('referralCode').textContent.trim();
    const text = `🎯 Rejoins COTA, la meilleure app de pronostics foot !\n\nCode parrainage : *${code}*\n\nTélécharge l'app → https://cota.app`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}
function shareGeneric() {
    const code = document.getElementById('referralCode').textContent.trim();
    if (navigator.share) {
        navigator.share({ title: 'COTA', text: `Utilise mon code ${code} sur COTA !`, url: 'https://cota.app' });
    } else {
        copyCode();
    }
}
</script>
@endpush
