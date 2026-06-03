<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — COTA</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <!-- Google Fonts — Archivo / Space Grotesk / JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg:     '#0b0d10',
                        'bg-2': '#15181d',
                        'bg-3': '#1a1e25',
                        line:   '#1d2026',
                        'line-2':'#2a2e36',
                        ink:    '#f4efe2',
                        'ink-2':'#c7c4b8',
                        dim:    '#8b8a85',
                        accent: '#e8ff36',
                        win:    '#3ddc91',
                        loss:   '#ff5b3a',
                    },
                    fontFamily: {
                        sans:  ['"Space Grotesk"', 'ui-sans-serif', 'system-ui'],
                        brand: ['Archivo', 'sans-serif'],
                        mono:  ['"JetBrains Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>

    <style>
        /* COTA tokens inlinés pour le layout */
        :root {
            --bg:#0b0d10; --bg-2:#15181d; --bg-3:#1a1e25;
            --line:#1d2026; --line-2:#2a2e36;
            --ink:#f4efe2; --ink-2:#c7c4b8; --dim:#8b8a85; --dim-2:#5a5d63;
            --accent:#e8ff36; --win:#3ddc91; --loss:#ff5b3a;
            --r-sm:6px; --r-md:10px; --r-lg:12px;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg); color: var(--ink); -webkit-font-smoothing: antialiased; }

        /* Sidebar */
        .sidebar { background: var(--bg); border-right: 1px solid var(--line); }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: var(--r-sm);
            color: var(--dim); font-size: 14px; font-weight: 500;
            text-decoration: none; transition: color .15s, background .15s;
            border-left: 2px solid transparent;
        }
        .sidebar-link:hover { color: var(--ink); background: var(--bg-2); }
        .sidebar-link.active { color: var(--accent); background: rgba(232,255,54,.08); border-left-color: var(--accent); }

        /* Buttons */
        .btn-primary { display:inline-flex; align-items:center; justify-content:center; height:48px; padding:0 24px; background:var(--accent); color:#0b0d10; font-weight:700; font-size:14px; border:none; border-radius:var(--r-md); cursor:pointer; transition:opacity .15s; text-decoration:none; }
        .btn-primary:hover { opacity:.88; }
        .btn-secondary { display:inline-flex; align-items:center; justify-content:center; height:40px; padding:0 20px; background:transparent; color:var(--ink); font-weight:600; font-size:13px; border:1px solid var(--line-2); border-radius:var(--r-md); cursor:pointer; transition:border-color .15s; text-decoration:none; }
        .btn-secondary:hover { border-color:var(--dim); background:var(--bg-2); }
        .btn-danger { display:inline-flex; align-items:center; justify-content:center; height:40px; padding:0 20px; background:rgba(255,91,58,.10); color:var(--loss); font-weight:600; font-size:13px; border:1px solid rgba(255,91,58,.30); border-radius:var(--r-md); cursor:pointer; text-decoration:none; transition:background .15s; }
        .btn-danger:hover { background:rgba(255,91,58,.18); }
        .btn-sm { height:32px !important; padding:0 14px !important; font-size:12px !important; }

        /* Cards */
        .card { background:var(--bg-2); border:1px solid var(--line); border-radius:var(--r-lg); padding:16px; }

        /* Badges */
        .badge-accent { display:inline-flex; align-items:center; padding:2px 8px; background:var(--accent); color:#0b0d10; font-size:11px; font-weight:800; border-radius:9999px; }
        .badge-win    { padding:2px 8px; background:rgba(61,220,145,.12); color:var(--win); font-size:11px; font-weight:700; border-radius:9999px; }
        .badge-loss   { padding:2px 8px; background:rgba(255,91,58,.12); color:var(--loss); font-size:11px; font-weight:700; border-radius:9999px; }
        .badge-pending{ padding:2px 8px; background:rgba(139,138,133,.15); color:var(--dim); font-size:11px; font-weight:700; border-radius:9999px; }
        .badge-live   { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; background:rgba(255,91,58,.15); color:var(--loss); font-size:11px; font-weight:700; border-radius:9999px; }
        .badge-live::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--loss); animation:pulse-dot 1.2s ease-in-out infinite; }

        /* Inputs */
        .input-brand { width:100%; height:44px; padding:0 14px; background:var(--bg); border:1px solid var(--line-2); border-radius:var(--r-md); color:var(--ink); font-family:inherit; font-size:14px; outline:none; transition:border-color .15s; }
        .input-brand:focus { border-color:var(--accent); }
        .input-brand::placeholder { color:var(--dim-2); }
        textarea.input-brand { height:auto; padding:12px 14px; resize:vertical; }
        select.input-brand { cursor:pointer; }
        .input-label { display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:var(--dim); letter-spacing:.05em; text-transform:uppercase; }

        /* Tables */
        .table-brand { width:100%; border-collapse:collapse; }
        .table-brand thead tr { background:var(--bg-3); }
        .table-brand thead th { padding:10px 14px; text-align:left; font-size:11px; font-weight:700; color:var(--dim); letter-spacing:.08em; text-transform:uppercase; border-bottom:1px solid var(--line); }
        .table-brand tbody tr { border-bottom:1px solid var(--line); transition:background .1s; }
        .table-brand tbody tr:hover { background:var(--bg-2); }
        .table-brand tbody td { padding:12px 14px; font-size:13px; color:var(--ink-2); }
        .table-brand .td-primary { color:var(--ink); font-weight:600; }

        /* Alertes */
        .alert-brand { display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:var(--r-md); margin-bottom:20px; font-size:13px; font-weight:500; }
        .alert-success { background:rgba(61,220,145,.10); border:1px solid rgba(61,220,145,.30); color:var(--win); }
        .alert-error   { background:rgba(255,91,58,.10);  border:1px solid rgba(255,91,58,.30);  color:var(--loss); }
        .alert-warning { background:rgba(232,255,54,.08); border:1px solid rgba(232,255,54,.25); color:var(--accent); }

        /* Charts */
        .chart-container { position:relative; width:100%; overflow:hidden; }

        /* Mobile sidebar */
        @media (max-width:1023px) {
            .sidebar { position:fixed; inset-y:0; left:0; z-index:50; transform:translateX(-100%); transition:transform .3s ease; }
            .sidebar.open { transform:translateX(0); }
            .main-content { margin-left:0 !important; }
            .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:40; }
            .sidebar-overlay.open { display:block; }
        }

        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }
    </style>

    @stack('styles')
</head>

<body class="h-full">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="flex h-full">
        <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
        <aside id="sidebar" class="sidebar w-64 flex flex-col lg:fixed lg:inset-y-0 lg:left-0 lg:translate-x-0">

            <!-- Wordmark -->
            <div class="flex items-center gap-3 h-16 px-5 border-b" style="border-color:var(--line)">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <rect width="28" height="28" rx="6" fill="#e8ff36"/>
                    <text x="14" y="20" text-anchor="middle" font-family="Archivo,sans-serif" font-weight="900" font-size="16" fill="#0b0d10">C</text>
                </svg>
                <span style="font-family:Archivo,sans-serif;font-weight:900;font-size:18px;color:var(--ink);letter-spacing:.04em">COTA <span style="color:var(--dim);font-size:12px;font-weight:600;letter-spacing:.12em">ADMIN</span></span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

                <a href="{{ route('admin.dashboard.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <!-- ── Pronostics ── -->
                <div class="pt-3 pb-1 px-2">
                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--dim-2)">Pronostics</span>
                </div>

                <a href="{{ route('admin.predictions.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.predictions*') ? 'active' : '' }}">
                    <i class="fa-solid fa-futbol w-4 text-center"></i>
                    <span>Pronostics</span>
                </a>

                <a href="{{ route('admin.stats.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.stats*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-column w-4 text-center"></i>
                    <span>Statistiques</span>
                </a>

                <a href="{{ route('admin.competitions.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.competitions*') ? 'active' : '' }}">
                    <i class="fa-solid fa-trophy w-4 text-center"></i>
                    <span>Compétitions</span>
                </a>

                <!-- ── Utilisateurs ── -->
                <div class="pt-3 pb-1 px-2">
                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--dim-2)">Utilisateurs</span>
                </div>

                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users w-4 text-center"></i>
                    <span>Utilisateurs</span>
                </a>

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                    <i class="fa-solid fa-credit-card w-4 text-center"></i>
                    <span>Abonnements</span>
                </a>

                <a href="{{ route('admin.referrals.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.referrals*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-arrows w-4 text-center"></i>
                    <span>Parrainages</span>
                </a>

                <a href="{{ route('admin.affiliates.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.affiliates*') ? 'active' : '' }}">
                    <i class="fa-solid fa-handshake w-4 text-center"></i>
                    <span>Affiliations</span>
                </a>

                <a href="{{ route('admin.feedbacks.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.feedbacks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots w-4 text-center"></i>
                    <span>Feedbacks</span>
                    @php $openCount = \App\Models\Feedback::where('status','open')->count(); @endphp
                    @if($openCount > 0)
                        <span class="ml-auto badge-accent">{{ $openCount }}</span>
                    @endif
                </a>

                <!-- ── Bookmakers ── -->
                <div class="pt-3 pb-1 px-2">
                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--dim-2)">Bookmakers</span>
                </div>

                <a href="{{ route('admin.admin.bookmakers.list') }}"
                   class="sidebar-link {{ request()->routeIs('admin.admin.bookmakers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-store w-4 text-center"></i>
                    <span>Bookmakers</span>
                </a>

                <a href="{{ route('admin.bookmaker-blogs.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.bookmaker-blogs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper w-4 text-center"></i>
                    <span>Articles</span>
                </a>

                <a href="{{ route('admin.bookmaker-candidates.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.bookmaker-candidates*') ? 'active' : '' }}">
                    <i class="fa-solid fa-magnifying-glass w-4 text-center"></i>
                    <span>Candidats</span>
                    @php
                        $pendingCandidates = \App\Models\BookmakerCandidate::where('status','pending')->count();
                    @endphp
                    @if($pendingCandidates > 0)
                        <span class="ml-auto badge-accent">{{ $pendingCandidates }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.coupon.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.coupon*') ? 'active' : '' }}">
                    <i class="fa-solid fa-ticket w-4 text-center"></i>
                    <span>Coupon IA</span>
                </a>

                <a href="{{ route('admin.news-sources.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.news-sources*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper w-4 text-center"></i>
                    <span>Actualités</span>
                    @php $pendingNews = \App\Models\NewsSource::active()->count(); @endphp
                    @if($pendingNews > 0)
                        <span class="ml-auto" style="font-size:10px;padding:1px 6px;border-radius:20px;background:rgba(232,255,54,.12);color:var(--accent);font-family:'JetBrains Mono',monospace">{{ $pendingNews }}</span>
                    @endif
                </a>

                <!-- ── Système ── -->
                <div class="pt-3 pb-1 px-2" style="border-top:1px solid var(--line); margin-top:8px">
                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--dim-2)">Système</span>
                </div>

                <a href="{{ route('admin.stats.funnel') }}"
                   class="sidebar-link {{ request()->routeIs('admin.stats.funnel') ? 'active' : '' }}">
                    <i class="fa-solid fa-filter w-4 text-center"></i>
                    <span>Funnel</span>
                </a>

                <a href="{{ route('admin.api-monitor.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.api-monitor*') ? 'active' : '' }}">
                    <i class="fa-solid fa-satellite-dish w-4 text-center"></i>
                    <span>Monitoring APIs</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cog w-4 text-center"></i>
                    <span>Paramètres</span>
                </a>
            </nav>

            <!-- User + logout -->
            <div class="p-4" style="border-top:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                         style="background:rgba(232,255,54,.12);border:1px solid rgba(232,255,54,.2)">
                        <i class="fa-solid fa-user-shield text-sm" style="color:var(--accent)"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:var(--ink)">{{ auth()->user()->name }}</p>
                        <p class="text-xs" style="color:var(--dim)">Super Admin</p>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-danger btn-sm" title="Déconnexion">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ── Main Content ────────────────────────────────────────────────── -->
        <div class="main-content flex-1 lg:ml-64 flex flex-col min-h-screen">

            <!-- Top Bar -->
            <header class="h-16 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-40"
                    style="background:var(--bg);border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 transition" style="color:var(--dim)"
                            aria-label="Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-lg font-semibold" style="color:var(--ink)">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="location.reload()" class="p-2 transition" style="color:var(--dim)" title="Rafraîchir">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    <span class="hidden sm:inline tag-mono">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </header>

            <!-- Alerts -->
            <div class="px-4 lg:px-6 pt-5">
                @if(session('success'))
                    <div class="alert-brand alert-success" id="alert-s">
                        <i class="fa-solid fa-check-circle shrink-0"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="document.getElementById('alert-s').remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fa-solid fa-times"></i></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert-brand alert-error" id="alert-e">
                        <i class="fa-solid fa-exclamation-circle shrink-0"></i>
                        <span>{{ session('error') }}</span>
                        <button onclick="document.getElementById('alert-e').remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fa-solid fa-times"></i></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert-brand alert-warning" id="alert-w">
                        <i class="fa-solid fa-exclamation-triangle shrink-0"></i>
                        <span>{{ session('warning') }}</span>
                        <button onclick="document.getElementById('alert-w').remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fa-solid fa-times"></i></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert-brand alert-error" id="alert-v">
                        <i class="fa-solid fa-exclamation-circle shrink-0"></i>
                        <div>
                            <p class="font-semibold mb-1">Erreurs de validation :</p>
                            <ul class="list-disc list-inside text-xs space-y-0.5">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                        <button onclick="document.getElementById('alert-v').remove()" class="ml-auto opacity-60 hover:opacity-100"><i class="fa-solid fa-times"></i></button>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 px-4 lg:px-6 py-5">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth < 1024) {
                document.querySelectorAll('.sidebar-link').forEach(l =>
                    l.addEventListener('click', () => setTimeout(toggleSidebar, 100))
                );
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    document.getElementById('sidebar').classList.remove('open');
                    document.getElementById('sidebarOverlay').classList.remove('open');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
