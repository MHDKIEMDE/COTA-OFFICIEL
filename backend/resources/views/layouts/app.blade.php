<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0d10">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'COTA — Pronostics Football IA' }}</title>

    <!-- Fonts — même stack que l'app Flutter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Styles -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        /* ── Tokens V6 — identiques à l'app Flutter ─────────────────── */
        :root {
            --bg:    #0b0d10;
            --bg2:   #15181d;
            --bg3:   #1e2028;
            --ink:   #f4efe2;
            --ink2:  #b8b4a8;
            --dim:   #8b8a85;
            --acc:   #e8ff36;
            --win:   #3ddc91;
            --loss:  #ff5b3a;
            --warn:  #f5a623;
            --line:  #2a2e36;
            --line2: #1d2026;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { visibility: hidden; opacity: 0; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Space Grotesk', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100dvh;
            overflow-x: hidden;
        }

        /* ── Layout wrapper ──────────────────────────────────────────── */
        .cota-layout {
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
            max-width: 430px;        /* largeur app mobile */
            margin: 0 auto;
            position: relative;
            background: var(--bg);
        }

        /* Sur grand écran : centré avec fond latéral */
        @media (min-width: 600px) {
            body {
                background: #07090b;
            }
            .cota-layout {
                border-left:  1px solid var(--line2);
                border-right: 1px solid var(--line2);
            }
        }

        /* ── Header identique à tous les onglets Flutter ────────────── */
        .cota-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px 4px;
            background: var(--bg);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .cota-header__title {
            font-family: 'Archivo', sans-serif;
            font-weight: 900;
            font-size: 20px;
            color: var(--ink);
            letter-spacing: -0.3px;
        }

        .cota-header__actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cota-header__btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ink2);
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .cota-header__btn:hover { border-color: var(--acc); color: var(--acc); }

        .cota-header__btn--accent {
            background: var(--acc);
            border-color: var(--acc);
            color: var(--bg);
            font-family: 'Archivo', sans-serif;
            font-weight: 900;
            font-size: 12px;
            width: auto;
            padding: 0 12px;
            gap: 4px;
        }
        .cota-header__btn--accent:hover { opacity: 0.88; color: var(--bg); }

        /* ── Main content ────────────────────────────────────────────── */
        .cota-main {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 80px; /* espace bottom nav */
        }

        /* ── Bottom Nav — identique Flutter CotaBottomNav ───────────── */
        .cota-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 430px;
            height: 64px;
            background: var(--bg2);
            border-top: 1px solid var(--line2);
            display: flex;
            align-items: stretch;
            z-index: 200;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .cota-nav__item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            text-decoration: none;
            color: var(--dim);
            transition: color 0.15s;
            position: relative;
        }
        .cota-nav__item.active { color: var(--acc); }
        .cota-nav__item:hover  { color: var(--ink2); }

        .cota-nav__icon { font-size: 22px; line-height: 1; }
        .cota-nav__label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* FAB coupon — bouton central surélevé */
        .cota-nav__fab {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            text-decoration: none;
            color: var(--dim);
            position: relative;
        }
        .cota-nav__fab-ring {
            width: 52px;
            height: 52px;
            background: var(--acc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg);
            font-size: 22px;
            position: absolute;
            top: -14px;
            border: 3px solid var(--bg);
            transition: transform 0.15s;
        }
        .cota-nav__fab:hover .cota-nav__fab-ring { transform: scale(1.06); }
        .cota-nav__fab-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 26px;
            color: var(--dim);
        }
        .cota-nav__fab.active .cota-nav__fab-label { color: var(--acc); }

        /* Badge live */
        .cota-nav__live-dot {
            width: 6px; height: 6px;
            background: var(--loss);
            border-radius: 50%;
            position: absolute;
            top: 8px;
            right: calc(50% - 14px);
        }

        /* Badge coupon (nouvelle prédiction) */
        .cota-nav__badge {
            position: absolute;
            top: 6px;
            right: calc(50% - 16px);
            min-width: 16px;
            height: 16px;
            background: var(--acc);
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            font-weight: 800;
            color: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        /* ── Utilitaires communs ─────────────────────────────────────── */
        .mono { font-family: 'JetBrains Mono', monospace; }
        .archivo { font-family: 'Archivo', sans-serif; }

        /* Cards */
        .c-card {
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
        }
        .c-card--accent { border-color: rgba(232,255,54,.25); }

        /* Chips */
        .c-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
            background: var(--bg3);
            border: 1px solid var(--line);
            color: var(--dim);
        }
        .c-chip--accent  { background: rgba(232,255,54,.12); border-color: rgba(232,255,54,.35); color: var(--acc); }
        .c-chip--win     { background: rgba(61,220,145,.10); border-color: rgba(61,220,145,.30); color: var(--win); }
        .c-chip--loss    { background: rgba(255,91,58,.10);  border-color: rgba(255,91,58,.30);  color: var(--loss); }

        /* Stars */
        .c-stars { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--acc); }

        /* Odds */
        .c-odds {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            color: var(--acc);
            padding: 4px 8px;
            border: 1px solid rgba(232,255,54,.35);
            border-radius: 6px;
        }

        /* Section title */
        .c-section {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 10px;
            font-weight: 700;
            color: var(--dim);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 0 16px;
            margin: 20px 0 10px;
        }

        /* Divider */
        .c-divider { height: 1px; background: var(--line2); margin: 0 16px; }

        /* Empty state */
        .c-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            gap: 12px;
            color: var(--dim);
            text-align: center;
        }
        .c-empty__icon { font-size: 40px; opacity: 0.4; }
        .c-empty__title {
            font-family: 'Archivo', sans-serif;
            font-size: 18px;
            font-weight: 900;
            color: var(--ink);
        }
        .c-empty__sub {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 13px;
            color: var(--dim);
            line-height: 1.55;
            max-width: 240px;
        }

        /* ── Search modal ────────────────────────────────────────────── */
        .search-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: var(--bg);
            z-index: 1000;
            max-width: 430px;
            left: 50%;
            transform: translateX(-50%);
        }
        .search-modal.open { display: flex; flex-direction: column; }
        .search-modal__bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--line2);
        }
        .search-modal__input {
            flex: 1;
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 14px;
            color: var(--ink);
            outline: none;
        }
        .search-modal__input:focus { border-color: var(--acc); }
        .search-modal__input::placeholder { color: var(--dim); }
        .search-modal__results { flex: 1; overflow-y: auto; }
    </style>
</head>
<body>

<div class="cota-layout">

    <!-- ── Header ─────────────────────────────────────────────────────── -->
    @hasSection('header')
        @yield('header')
    @else
        <header class="cota-header">
            <span class="cota-header__title">
                @yield('page_title', 'Pronostics')
            </span>
            <div class="cota-header__actions">
                @yield('header_actions')
                <button class="cota-header__btn" onclick="openSearch()" aria-label="Rechercher">
                    <i class="bi bi-search"></i>
                </button>
                @auth
                    <a href="{{ route('profile') }}" class="cota-header__btn" aria-label="Profil">
                        <i class="bi bi-person"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="cota-header__btn" aria-label="Connexion">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                @endauth
            </div>
        </header>
    @endif

    <!-- ── Contenu principal ──────────────────────────────────────────── -->
    <main class="cota-main">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- ── Bottom Nav ─────────────────────────────────────────────────── -->
    <nav class="cota-nav">
        {{-- Pronostics --}}
        <a href="{{ route('home') }}"
           class="cota-nav__item {{ request()->routeIs('home') || request()->routeIs('predictions.*') ? 'active' : '' }}">
            <span class="cota-nav__icon"><i class="bi bi-grid-3x3-gap{{ request()->routeIs('home') || request()->routeIs('predictions.*') ? '-fill' : '' }}"></i></span>
            <span class="cota-nav__label">Accueil</span>
        </a>

        {{-- Live --}}
        <a href="{{ route('live') }}"
           class="cota-nav__item {{ request()->routeIs('live') ? 'active' : '' }}">
            @if(true) {{-- live dot toujours visible --}}
                <span class="cota-nav__live-dot"></span>
            @endif
            <span class="cota-nav__icon"><i class="bi bi-broadcast{{ request()->routeIs('live') ? '-pin' : '' }}"></i></span>
            <span class="cota-nav__label">Live</span>
        </a>

        {{-- Coupon — FAB central --}}
        @php $couponRoute = Route::has('coupon') ? route('coupon') : route('favorites'); @endphp
        <a href="{{ $couponRoute }}"
           class="cota-nav__fab {{ request()->routeIs('coupon') || request()->routeIs('favorites') ? 'active' : '' }}">
            <div class="cota-nav__fab-ring">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <span class="cota-nav__fab-label">Coupon</span>
        </a>

        {{-- Historique --}}
        <a href="{{ route('history') }}"
           class="cota-nav__item {{ request()->routeIs('history') ? 'active' : '' }}">
            <span class="cota-nav__icon"><i class="bi bi-clock{{ request()->routeIs('history') ? '-fill' : '' }}"></i></span>
            <span class="cota-nav__label">Historique</span>
        </a>

        {{-- Profil --}}
        <a href="{{ route('profile') }}"
           class="cota-nav__item {{ request()->routeIs('profile') || request()->routeIs('statistics') || request()->routeIs('subscription') || request()->routeIs('referral') ? 'active' : '' }}">
            <span class="cota-nav__icon"><i class="bi bi-person{{ request()->routeIs('profile') || request()->routeIs('statistics') || request()->routeIs('subscription') || request()->routeIs('referral') ? '-fill' : '' }}"></i></span>
            <span class="cota-nav__label">Profil</span>
        </a>
    </nav>

</div>

<!-- ── Search Modal ───────────────────────────────────────────────────── -->
<div class="search-modal" id="searchModal">
    <div class="search-modal__bar">
        <input type="text"
               class="search-modal__input"
               placeholder="Équipe, compétition..."
               id="searchInput"
               autocomplete="off">
        <button class="cota-header__btn" onclick="closeSearch()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="search-modal__results" id="searchResults">
        <p class="c-section">Recherches populaires</p>
        @foreach([
            ['🏴󠁧󠁢󠁥󠁮󠁧󠁿','Premier League'],
            ['🇪🇸','La Liga'],
            ['🇩🇪','Bundesliga'],
            ['🇮🇹','Serie A'],
            ['🇫🇷','Ligue 1'],
            ['🏆','Champions League'],
        ] as [$flag, $name])
        <a href="{{ route('home', ['competition' => $name]) }}"
           onclick="closeSearch()"
           style="display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;border-bottom:1px solid var(--line2);">
            <span style="font-size:20px;">{{ $flag }}</span>
            <span style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;color:var(--ink);">{{ $name }}</span>
        </a>
        @endforeach
    </div>
</div>

<!-- Scripts -->
<script>
    (function() {
        document.documentElement.style.visibility = 'visible';
        document.documentElement.style.opacity    = '1';
    })();

    function openSearch() {
        document.getElementById('searchModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => document.getElementById('searchInput')?.focus());
    }
    function closeSearch() {
        document.getElementById('searchModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSearch(); }, { passive: true });
</script>

@livewireScripts
@stack('scripts')
</body>
</html>
