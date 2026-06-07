@extends('layouts.app')

@php $hideDate = true; @endphp

@section('page_title', 'Profil')

@section('header_actions')
    <a href="{{ route('statistics') }}" class="cota-header__btn" aria-label="Statistiques">
        <i class="bi bi-bar-chart-line"></i>
    </a>
@endsection

@section('content')
<style>
    .p-avatar {
        width: 72px; height: 72px;
        border-radius: 50%;
        background: var(--acc);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Archivo', sans-serif;
        font-size: 24px; font-weight: 900;
        color: var(--bg);
        flex-shrink: 0;
        border: 3px solid var(--bg2);
    }
    .p-menu-section {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 10px; font-weight: 700;
        color: var(--dim);
        letter-spacing: 1.2px;
        text-transform: uppercase;
        padding: 20px 16px 8px;
    }
    .p-menu {
        background: var(--bg2);
        border: 1px solid var(--line);
        border-radius: 10px;
        margin: 0 16px;
        overflow: hidden;
    }
    .p-menu-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        text-decoration: none;
        color: var(--ink);
        border-bottom: 1px solid var(--line2);
        transition: background .1s;
    }
    .p-menu-item:last-child { border-bottom: none; }
    .p-menu-item:hover { background: var(--bg3); }
    .p-menu-item__icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .p-menu-item__label {
        flex: 1;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 14px; font-weight: 600;
    }
    .p-menu-item__badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px; font-weight: 800;
        background: var(--acc);
        color: var(--bg);
        padding: 2px 7px;
        border-radius: 4px;
    }
    .p-menu-item__chevron {
        color: var(--dim);
        font-size: 12px;
    }
</style>

@auth
    {{-- Hero profil --}}
    <div style="padding:20px 16px 16px;display:flex;align-items:center;gap:16px;">
        <div class="p-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
        <div style="flex:1;min-width:0;">
            <div style="font-family:'Archivo',sans-serif;font-size:18px;font-weight:900;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ auth()->user()->name ?? 'Utilisateur' }}
            </div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:12px;color:var(--dim);margin-top:2px;">
                {{ auth()->user()->phone ?? auth()->user()->email ?? '—' }}
            </div>
            <div style="margin-top:8px;">
                @if(auth()->user()->is_premium ?? false)
                    <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(232,255,54,.12);border:1px solid rgba(232,255,54,.3);color:var(--acc);font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;">
                        ⭐ PREMIUM
                    </span>
                @else
                    <a href="{{ route('subscription') }}" style="display:inline-flex;align-items:center;gap:5px;background:var(--bg3);border:1px solid var(--line);color:var(--dim);font-family:'Space Grotesk',sans-serif;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;text-decoration:none;">
                        ↑ Passer Premium
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="c-divider"></div>

    {{-- Compte --}}
    <p class="p-menu-section">Compte</p>
    <div class="p-menu">
        <a href="{{ route('subscription') }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:rgba(232,255,54,.10);color:var(--acc);">
                <i class="bi bi-crown-fill"></i>
            </div>
            <span class="p-menu-item__label">Abonnement</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
        <a href="{{ route('referral') }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:rgba(61,220,145,.10);color:var(--win);">
                <i class="bi bi-gift-fill"></i>
            </div>
            <span class="p-menu-item__label">Parrainage</span>
            <span class="p-menu-item__badge">+7j</span>
            <i class="bi bi-chevron-right p-menu-item__chevron" style="margin-left:8px;"></i>
        </a>
        <a href="#" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:var(--bg3);color:var(--dim);">
                <i class="bi bi-bell-fill"></i>
            </div>
            <span class="p-menu-item__label">Notifications</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
    </div>

    {{-- Activité --}}
    <p class="p-menu-section">Activité</p>
    <div class="p-menu">
        <a href="{{ route('statistics') }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:rgba(61,220,145,.10);color:var(--win);">
                <i class="bi bi-bar-chart-line-fill"></i>
            </div>
            <span class="p-menu-item__label">Statistiques</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
        <a href="{{ route('history') }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:var(--bg3);color:var(--dim);">
                <i class="bi bi-clock-history"></i>
            </div>
            <span class="p-menu-item__label">Historique</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
        <a href="{{ route('favorites') }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:rgba(232,255,54,.07);color:var(--acc);">
                <i class="bi bi-star-fill"></i>
            </div>
            <span class="p-menu-item__label">Favoris</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
    </div>

    {{-- Aide --}}
    <p class="p-menu-section">Aide</p>
    <div class="p-menu">
        <a href="#" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:var(--bg3);color:var(--dim);">
                <i class="bi bi-question-circle"></i>
            </div>
            <span class="p-menu-item__label">FAQ</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
        <a href="#" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:var(--bg3);color:var(--dim);">
                <i class="bi bi-headset"></i>
            </div>
            <span class="p-menu-item__label">Support</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
        <a href="{{ route('privacy') ?? '#' }}" class="p-menu-item">
            <div class="p-menu-item__icon" style="background:var(--bg3);color:var(--dim);">
                <i class="bi bi-file-text"></i>
            </div>
            <span class="p-menu-item__label">Confidentialité</span>
            <i class="bi bi-chevron-right p-menu-item__chevron"></i>
        </a>
    </div>

    {{-- Déconnexion --}}
    <div style="padding:20px 16px 8px;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="width:100%;padding:13px;background:rgba(255,91,58,.08);border:1px solid rgba(255,91,58,.25);border-radius:10px;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:700;color:var(--loss);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </button>
        </form>
    </div>

    <p style="text-align:center;padding:0 16px 32px;font-family:'JetBrains Mono',monospace;font-size:9px;color:var(--dim);letter-spacing:1.5px;">
        COTA v1.0.0 · © 2026
    </p>

@else
    <div class="c-empty" style="min-height:60vh;">
        <div class="c-empty__icon"><i class="bi bi-person-circle"></i></div>
        <div class="c-empty__title">Connecte-toi</div>
        <div class="c-empty__sub">Accède à ton profil, historique et statistiques personnalisées.</div>
        <a href="{{ route('login') }}" style="margin-top:12px;padding:12px 28px;background:var(--acc);border-radius:10px;font-family:'Archivo',sans-serif;font-size:14px;font-weight:900;color:var(--bg);text-decoration:none;">
            Se connecter →
        </a>
    </div>
@endauth
@endsection
