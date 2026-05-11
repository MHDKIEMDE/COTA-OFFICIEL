<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0D1117">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'COTA - Pronostics Football' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        /* Prevent FOUC */
        html { visibility: hidden; opacity: 0; }
    </style>
</head>
<body>

    <!-- Desktop Layout Wrapper -->
    <div class="desktop-layout">

    <!-- ===== DESKTOP SIDEBAR (masqué sur mobile) ===== -->
    <aside class="desktop-sidebar">
        <!-- Logo -->
        <div class="desktop-sidebar__brand">
            <div class="desktop-sidebar__logo-mark">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <span class="desktop-sidebar__logo-text">COTA</span>
        </div>

        <!-- Navigation principale -->
        <nav class="desktop-sidebar__nav">
            <p class="desktop-sidebar__section-label">NAVIGATION</p>

            <a href="{{ route('home') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('home') || request()->routeIs('predictions.index') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Pronostics</span>
            </a>

            <a href="{{ route('live') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('live') ? 'active' : '' }}">
                <i class="bi bi-broadcast"></i>
                <span>Direct</span>
                <span class="desktop-sidebar__live-badge">LIVE</span>
            </a>

            <a href="{{ route('favorites') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('favorites') ? 'active' : '' }}">
                <i class="bi bi-star-fill"></i>
                <span>Favoris</span>
            </a>

            <a href="{{ route('competitions') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('competitions') ? 'active' : '' }}">
                <i class="bi bi-trophy-fill"></i>
                <span>Ligues</span>
            </a>

            <p class="desktop-sidebar__section-label" style="margin-top: 1.5rem;">COMPTE</p>

            <a href="{{ route('history') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('history') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Historique</span>
            </a>

            <a href="{{ route('statistics') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('statistics') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Statistiques</span>
            </a>

            <a href="{{ route('subscription') }}"
               class="desktop-sidebar__nav-item {{ request()->routeIs('subscription') ? 'active' : '' }}">
                <i class="bi bi-crown-fill"></i>
                <span>Premium</span>
            </a>
        </nav>

        <!-- Bas de sidebar -->
        <div class="desktop-sidebar__footer">
            @auth
                <a href="{{ route('profile') }}" class="desktop-sidebar__profile">
                    <div class="desktop-sidebar__avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="desktop-sidebar__profile-info">
                        <span class="desktop-sidebar__profile-name">{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                        <span class="desktop-sidebar__profile-status">
                            @if(auth()->user()->is_premium ?? false)
                                ⭐ Premium
                            @else
                                Gratuit
                            @endif
                        </span>
                    </div>
                    <i class="bi bi-chevron-right" style="color: var(--cota-text-muted); font-size: 0.75rem;"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="desktop-sidebar__login-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Se connecter</span>
                </a>
            @endauth
        </div>
    </aside>

    <!-- App Container -->
    <div class="app-container" id="app">
        
        <!-- Header -->
        @hasSection('header')
            <header class="app-header">
                @yield('header')
            </header>
        @else
            <header class="app-header">
                <div class="app-header__sport-selector">
                    <i class="bi bi-globe"></i>
                    <span>Football</span>
                    <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                </div>
                
                <div class="app-header__actions">
                    <button class="app-header__btn" onclick="openSearch()">
                        <i class="bi bi-search"></i>
                    </button>
                    @auth
                        <a href="{{ route('profile') }}" class="app-header__btn">
                            <i class="bi bi-person-circle"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="app-header__btn">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                    @endauth
                </div>
            </header>
        @endif
        
        <!-- Date Selector -->
        @if(!isset($hideDate) || !$hideDate)
        <div class="date-selector" id="dateSelector">
            @php
                $today = now();
                $dates = [];
                for ($i = -3; $i <= 7; $i++) {
                    $dates[] = $today->copy()->addDays($i);
                }
            @endphp
            
            @foreach($dates as $date)
                <a href="?date={{ $date->format('Y-m-d') }}" 
                   class="date-selector__item {{ $date->isToday() ? 'today' : '' }} {{ (request('date', $today->format('Y-m-d')) == $date->format('Y-m-d')) ? 'active' : '' }}">
                    <span class="date-selector__day">{{ $date->locale('fr')->isoFormat('ddd') }}</span>
                    <span class="date-selector__date">{{ $date->format('d.m') }}</span>
                </a>
            @endforeach
        </div>
        @endif
        
        <!-- Main Content -->
        <main class="app-main">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav" id="bottomNav">
            <a href="{{ route('home') }}" class="bottom-nav__item {{ request()->routeIs('home') || request()->routeIs('predictions.index') || request()->routeIs('predictions.show') ? 'active' : '' }}">
                <span class="bottom-nav__icon"><i class="bi bi-grid-3x3-gap"></i></span>
                <span class="bottom-nav__label">Tous</span>
            </a>
            
            <a href="{{ route('live') }}" class="bottom-nav__item bottom-nav__item--live {{ request()->routeIs('live') ? 'active' : '' }}">
                <span class="bottom-nav__icon"><i class="bi bi-broadcast"></i></span>
                <span class="bottom-nav__label">Direct</span>
            </a>
            
            {{-- Center Button (Favorites) --}}
            <a href="{{ route('favorites') }}" class="bottom-nav__item bottom-nav__center {{ request()->routeIs('favorites') ? 'active' : '' }}">
                <span class="bottom-nav__icon-wrapper">
                    <i class="bi bi-star-fill"></i>
                </span>
                <span class="bottom-nav__label" style="margin-top: 4px;">Favoris</span>
            </a>
            
            <a href="{{ route('competitions') }}" class="bottom-nav__item {{ request()->routeIs('competitions') ? 'active' : '' }}">
                <span class="bottom-nav__icon"><i class="bi bi-trophy"></i></span>
                <span class="bottom-nav__label">Ligues</span>
            </a>
            
            <a href="{{ route('profile') }}" class="bottom-nav__item {{ request()->routeIs('profile') || request()->routeIs('statistics') || request()->routeIs('history') || request()->routeIs('subscription') || request()->routeIs('referral') ? 'active' : '' }}">
                <span class="bottom-nav__icon"><i class="bi bi-person"></i></span>
                <span class="bottom-nav__label">Menu</span>
            </a>
        </nav>
    </div>
    
    </div>{{-- /app-container --}}

    <!-- ===== DESKTOP RIGHT PANEL (masqué sur mobile) ===== -->
    <aside class="desktop-right">

        <!-- Widget Performance -->
        <div class="desktop-right__card">
            <div class="desktop-right__card-header">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Performance du jour</span>
            </div>
            <div class="desktop-right__perf">
                <div class="desktop-right__perf-ring">
                    <svg viewBox="0 0 44 44" width="72" height="72">
                        <circle cx="22" cy="22" r="18" fill="none" stroke="var(--cota-bg-elevated)" stroke-width="4"/>
                        <circle cx="22" cy="22" r="18" fill="none" stroke="#10B981" stroke-width="4"
                            stroke-dasharray="84.82 28.27" stroke-dashoffset="21.2" stroke-linecap="round"
                            transform="rotate(-90 22 22)"/>
                    </svg>
                    <span class="desktop-right__perf-pct">75%</span>
                </div>
                <div class="desktop-right__perf-stats">
                    <div class="desktop-right__perf-stat">
                        <span class="desktop-right__perf-val" style="color:#10B981">12</span>
                        <span class="desktop-right__perf-label">Gagnés</span>
                    </div>
                    <div class="desktop-right__perf-stat">
                        <span class="desktop-right__perf-val" style="color:#EF4444">4</span>
                        <span class="desktop-right__perf-label">Perdus</span>
                    </div>
                    <div class="desktop-right__perf-stat">
                        <span class="desktop-right__perf-val" style="color:#F59E0B">2</span>
                        <span class="desktop-right__perf-label">Nuls</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Premium -->
        <div class="desktop-right__premium">
            <div class="desktop-right__premium-glow"></div>
            <div class="desktop-right__premium-content">
                <div class="desktop-right__premium-icon">⭐</div>
                <h4 class="desktop-right__premium-title">Passez Premium</h4>
                <p class="desktop-right__premium-text">
                    Pronostics 4 étoiles, analyses complètes et accès illimité.
                </p>
                <a href="{{ route('subscription') }}" class="desktop-right__premium-btn">
                    Voir les offres
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Ligues populaires -->
        <div class="desktop-right__card">
            <div class="desktop-right__card-header">
                <i class="bi bi-trophy-fill"></i>
                <span>Ligues populaires</span>
            </div>
            <div class="desktop-right__leagues">
                @foreach([
                    ['🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'Premier League'],
                    ['🇪🇸', 'La Liga'],
                    ['🇩🇪', 'Bundesliga'],
                    ['🇮🇹', 'Serie A'],
                    ['🇫🇷', 'Ligue 1'],
                    ['🏆', 'UEFA Champions League'],
                ] as [$flag, $league])
                    <a href="{{ route('home', ['competition' => $league]) }}"
                       class="desktop-right__league-item">
                        <span class="desktop-right__league-flag">{{ $flag }}</span>
                        <span class="desktop-right__league-name">{{ $league }}</span>
                        <i class="bi bi-chevron-right desktop-right__league-arrow"></i>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Recherche rapide -->
        <div class="desktop-right__search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Rechercher équipe, ligue..." id="desktopSearch">
        </div>

    </aside>

    </div>{{-- /desktop-layout --}}

    <!-- Search Modal -->
    <div class="search-modal" id="searchModal" style="display: none;">
        <div class="search-page">
            <div class="search-page__input-wrapper">
                <i class="bi bi-search search-page__icon"></i>
                <input type="text" 
                       class="search-page__input" 
                       placeholder="Rechercher équipe, compétition..."
                       id="searchInput"
                       autocomplete="off">
                <button class="app-header__btn" onclick="closeSearch()" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="cota-tabs">
                <a href="#" class="cota-tabs__item active">TOUT</a>
                <a href="#" class="cota-tabs__item">ÉQUIPES</a>
                <a href="#" class="cota-tabs__item">JOUEURS</a>
                <a href="#" class="cota-tabs__item">COMPÉTITIONS</a>
            </div>
            
            <p class="search-page__section-title">RECHERCHES LES PLUS POPULAIRES</p>
            
            <div id="searchResults">
                <!-- Popular searches -->
                @php
                    $popularSearches = [
                        ['name' => 'CAN', 'icon' => '🏆', 'type' => 'FOOTBALL'],
                        ['name' => 'Premier League', 'icon' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'type' => 'FOOTBALL'],
                        ['name' => 'La Liga', 'icon' => '🇪🇸', 'type' => 'FOOTBALL'],
                        ['name' => 'Serie A', 'icon' => '🇮🇹', 'type' => 'FOOTBALL'],
                        ['name' => 'Ligue 1', 'icon' => '🇫🇷', 'type' => 'FOOTBALL'],
                        ['name' => 'Bundesliga', 'icon' => '🇩🇪', 'type' => 'FOOTBALL'],
                    ];
                @endphp
                
                @foreach($popularSearches as $search)
                    <a href="{{ route('predictions.index') }}?search={{ urlencode($search['name']) }}" class="search-result">
                        <span class="search-result__icon" style="font-size: 1.5rem;">{{ $search['icon'] }}</span>
                        <div class="search-result__info">
                            <div class="search-result__name">{{ $search['name'] }}</div>
                            <div class="search-result__type">{{ $search['type'] }}</div>
                        </div>
                        <i class="bi bi-star search-result__favorite"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Scripts - Chargement différé pour améliorer les performances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <script>
        // Prevent FOUC - Exécution immédiate (critique)
        (function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    document.documentElement.style.visibility = 'visible';
                    document.documentElement.style.opacity = '1';
                });
            } else {
                document.documentElement.style.visibility = 'visible';
                document.documentElement.style.opacity = '1';
            }
        })();
        
        // Search Modal - Fonctions légères
        function openSearch() {
            const modal = document.getElementById('searchModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    const input = document.getElementById('searchInput');
                    if (input) input.focus();
                });
            }
        }
        
        function closeSearch() {
            const modal = document.getElementById('searchModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // Close search on escape - Utiliser une seule fonction pour tous les événements clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSearch();
            }
        }, { passive: true });
        
        // Scroll date selector - Déféré pour ne pas bloquer le rendu
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDateSelector);
        } else {
            requestIdleCallback(initDateSelector, { timeout: 1000 });
        }
        
        function initDateSelector() {
            const dateSelector = document.getElementById('dateSelector');
            if (!dateSelector) return;
            
            const activeDate = dateSelector.querySelector('.active') || dateSelector.querySelector('.today');
            if (activeDate) {
                requestAnimationFrame(() => {
                    const scrollLeft = activeDate.offsetLeft - (dateSelector.offsetWidth / 2) + (activeDate.offsetWidth / 2);
                    dateSelector.scrollTo({ left: scrollLeft, behavior: 'auto' }); // 'auto' plus rapide que 'smooth'
                });
            }
        }
        
        // Pull to refresh - Optimisé avec passive listeners
        let startY = 0;
        document.addEventListener('touchstart', e => {
            startY = e.touches[0].pageY;
        }, { passive: true });
        
        document.addEventListener('touchmove', e => {
            if (window.scrollY === 0 && e.touches[0].pageY > startY + 100) {
                // Could trigger refresh here
            }
        }, { passive: true });
    </script>
    
    <style>
        /* Search Modal Styles */
        .search-modal {
            position: fixed;
            inset: 0;
            background: var(--cota-bg-primary);
            z-index: 2000;
            overflow-y: auto;
        }
        
        /* Smooth transitions */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        .app-container {
            min-height: 100vh;
            min-height: 100dvh;
        }
    </style>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    @stack('scripts')
</body>
</html>
